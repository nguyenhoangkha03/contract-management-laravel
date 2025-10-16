<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContractApiController extends Controller
{
    /**
     * Get contracts for a specific client by email
     */
    public function getContractsByEmail(Request $request): JsonResponse
    {
        try {
            $email = $request->query('email');
            
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
            }

            Log::info("API: Getting contracts for email: {$email}");

            // Find client by email
            $client = Client::where('email', $email)->first();
            
            if (!$client) {
                Log::warning("API: Client not found for email: {$email}");
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'client' => null,
                    'message' => 'Client not found',
                    'debug' => [
                        'email' => $email,
                        'client_found' => false
                    ]
                ]);
            }

            Log::info("API: Client found - {$client->name} (ID: {$client->id})");

            // Get contracts for this client with related data
            $contracts = Contract::with([
                'client:id,name,email,phone,address',
                'contractType:id,name',
                'contractStatus:id,name,code',
                'contractProducts.product:id,name,unit',
                'payments:id,contract_id,amount_paid,payment_date,method'
            ])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

            Log::info("API: Found {$contracts->count()} contracts for client {$client->name}");

            // Format contracts data
            $formattedContracts = $contracts->map(function ($contract) {
                // Calculate progress based on payments
                $totalPaid = $contract->payments()->sum('amount_paid') ?? 0;
                $progress = $contract->total_value > 0 ? min(($totalPaid / $contract->total_value) * 100, 100) : 0;
                
                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'contract_type_name' => $contract->contractType?->name ?? 'Chưa xác định',
                    'contract_status_name' => $contract->contractStatus?->name ?? 'Chưa xác định',
                    'contract_status_code' => $contract->contractStatus?->code ?? 'unknown',
                    'total_value' => (float) $contract->total_value ?? 0,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'sign_date' => $contract->sign_date,
                    'contract_purpose' => $contract->contract_purpose,
                    'payment_method' => $contract->pay_method,
                    'payment_terms' => $contract->payment_terms,
                    'progress' => round($progress, 2),
                    'total_paid' => (float) $totalPaid,
                    'remaining_amount' => (float) $contract->total_value - $totalPaid,
                    'products_count' => $contract->contractProducts->count(),
                    'payments_count' => $contract->payments->count(),
                    'client_name' => $contract->client->name,
                    'client_email' => $contract->client->email,
                    'created_at' => $contract->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $contract->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            // Calculate statistics
            $totalValue = $formattedContracts->sum('total_value');
            $activeContracts = $formattedContracts->filter(function ($contract) {
                $status = strtolower($contract['contract_status_code'] ?? '');
                return str_contains($status, 'active') || 
                       str_contains($status, 'dang_thuc_hien') ||
                       str_contains($status, 'executing');
            })->count();

            $completedContracts = $formattedContracts->filter(function ($contract) {
                $status = strtolower($contract['contract_status_code'] ?? '');
                return str_contains($status, 'completed') || 
                       str_contains($status, 'hoan_thanh') ||
                       str_contains($status, 'finished');
            })->count();

            $pendingContracts = $formattedContracts->filter(function ($contract) {
                $status = strtolower($contract['contract_status_code'] ?? '');
                return str_contains($status, 'pending') || 
                       str_contains($status, 'cho_duyet') ||
                       str_contains($status, 'waiting');
            })->count();

            $statistics = [
                'total' => $formattedContracts->count(),
                'total_value' => $totalValue,
                'active' => $activeContracts,
                'completed' => $completedContracts,
                'pending' => $pendingContracts,
            ];

            Log::info("API: Statistics calculated", $statistics);

            return response()->json([
                'success' => true,
                'data' => $formattedContracts->values(),
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                ],
                'statistics' => $statistics,
                'message' => $formattedContracts->count() > 0 
                    ? "Found {$formattedContracts->count()} contracts for {$client->name}" 
                    : "No contracts found for {$client->name}",
                'debug' => [
                    'email' => $email,
                    'client_found' => true,
                    'client_name' => $client->name,
                    'contracts_count' => $formattedContracts->count(),
                    'query_time' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching contracts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get contract details by ID
     */
    public function getContractDetail(Request $request, $id): JsonResponse
    {
        try {
            $email = $request->query('email');
            
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
            }

            Log::info("API: Getting contract detail for ID: {$id}, Email: {$email}");

            // Find client by email
            $client = Client::where('email', $email)->first();
            
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            // Get contract details with all relations
            $contract = Contract::with([
                'client:id,name,email,phone,address',
                'contractType:id,name',
                'contractStatus:id,name,code',
                'contractProducts.product:id,name,unit',
                'payments' => function($query) {
                    $query->orderBy('payment_date', 'desc');
                },
                'contractParticipants',
                'contractAttachments'
            ])
            ->where('id', $id)
            ->where('client_id', $client->id)
            ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract not found or access denied'
                ], 404);
            }

            // Calculate progress and payment info
            $totalPaid = $contract->payments()->sum('amount_paid') ?? 0;
            $progress = $contract->total_value > 0 ? min(($totalPaid / $contract->total_value) * 100, 100) : 0;

            $contractDetail = [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'contract_type_name' => $contract->contractType?->name,
                'contract_status_name' => $contract->contractStatus?->name,
                'contract_status_code' => $contract->contractStatus?->code,
                'contract_purpose' => $contract->contract_purpose,
                'total_value' => (float) $contract->total_value,
                'start_date' => $contract->start_date,
                'end_date' => $contract->end_date,
                'sign_date' => $contract->sign_date,
                'payment_method' => $contract->pay_method,
                'payment_terms' => $contract->payment_terms,
                'legal_basis' => $contract->legal_basis,
                'progress' => round($progress, 2),
                'total_paid' => (float) $totalPaid,
                'remaining_amount' => (float) $contract->total_value - $totalPaid,
                'products' => $contract->contractProducts->map(function($product) {
                    return [
                        'id' => $product->id,
                        'product_name' => $product->product?->name,
                        'quantity' => $product->number,
                        'unit' => $product->product?->unit,
                        'unit_price' => (float) $product->unit_price ?? 0,
                        'total' => (float) $product->total,
                        'description' => $product->description,
                    ];
                }),
                'payments' => $contract->payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount_paid,
                        'payment_date' => $payment->payment_date,
                        'method' => $payment->method,
                        'note' => $payment->note ?? '',
                    ];
                }),
                'participants' => $contract->contractParticipants->map(function($participant) {
                    return [
                        'id' => $participant->id,
                        'party_type' => $participant->party_type,
                        'full_name' => $participant->full_name,
                        'representative_position' => $participant->representative_position,
                        'phone' => $participant->phone,
                        'email' => $participant->email,
                        'address' => $participant->address,
                        'tax_code' => $participant->tax_code,
                        'bank_account' => $participant->bank_account,
                        'bank_name' => $participant->bank_name,
                    ];
                }),
                'attachments' => $contract->contractAttachments->map(function($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'file_path' => $attachment->file_path,
                        'uploaded_by' => $attachment->uploaded_by,
                        'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'created_at' => $contract->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $contract->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'data' => $contractDetail
            ]);

        } catch (\Exception $e) {
            Log::error('API Error fetching contract detail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching contract details'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for a client
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $email = $request->query('email');
            
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
            }

            // Find client
            $client = Client::where('email', $email)->first();
            
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            // Get basic contract stats
            $totalContracts = Contract::where('client_id', $client->id)->count();
            $totalValue = Contract::where('client_id', $client->id)->sum('total_value') ?? 0;
            
            // Get status-based counts
            $activeContracts = Contract::where('client_id', $client->id)
                ->whereHas('contractStatus', function($query) {
                    $query->where('code', 'like', '%active%')
                          ->orWhere('code', 'like', '%dang_thuc_hien%');
                })->count();

            $completedContracts = Contract::where('client_id', $client->id)
                ->whereHas('contractStatus', function($query) {
                    $query->where('code', 'like', '%completed%')
                          ->orWhere('code', 'like', '%hoan_thanh%');
                })->count();

            $pendingContracts = Contract::where('client_id', $client->id)
                ->whereHas('contractStatus', function($query) {
                    $query->where('code', 'like', '%pending%')
                          ->orWhere('code', 'like', '%cho_duyet%');
                })->count();

            $stats = [
                'total_contracts' => $totalContracts,
                'total_value' => (float) $totalValue,
                'active_contracts' => $activeContracts,
                'completed_contracts' => $completedContracts,
                'pending_contracts' => $pendingContracts,
                'documents' => $totalContracts * 2, // Estimate
                'support_requests' => 0, // Would need separate table
                'unpaid_invoices' => max(0, $totalContracts - $completedContracts)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error fetching dashboard stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching statistics'
            ], 500);
        }
    }

    /**
     * Test endpoint to verify API is working
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Contract API is working',
            'timestamp' => now()->toISOString(),
            'endpoints' => [
                'GET /api/contracts/by-email?email={email}' => 'Get contracts by client email',
                'GET /api/contracts/{id}/detail?email={email}' => 'Get contract details',
                'GET /api/contracts/dashboard-stats?email={email}' => 'Get dashboard statistics',
                'GET /api/contracts/test' => 'Test API connection'
            ]
        ]);
    }
}