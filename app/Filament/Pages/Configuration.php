<?php

namespace App\Filament\Pages;

use App\Models\Contract;
use App\Models\ContractParticipant;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\ContractStatus;
use App\Models\ContractType;
use App\Models\ExportTemplate;
use App\Models\Feature;
use App\Models\GeneralConfiguration;
use App\Models\GeneralConfigurationAlertTarget;
use App\Models\GeneralConfigurationAutoUpdate;
use App\Models\GeneralConfigurationNotification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supervisor;
use App\Models\User;
use Filament\Forms\Components\View;
use Filament\Forms\Get;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

class Configuration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Cấu Hình Chung';
    protected static ?string $title = 'Cấu Hình Chung';
    protected static ?string $navigationGroup = 'Cấu Hình';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.configuration';

    public $data = [];
    public $userRoles = [];
    public $statusOptions = [];

    public function mount(): void
    {
        $this->userRoles = $this->loadRoles();

        $this->statusOptions = $this->loadStatuses();

        $configurations = $this->loadConfigurations();

        $this->form->fill($configurations);
    }

    protected function loadRoles(): array
    {
        try {
            if (Schema::hasTable('roles')) {
                return Role::pluck('name', 'code')->toArray();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error loading roles: " . $e->getMessage());
        }

        return [
            'quanly' => 'Quản lý',
            'kinhdoanh' => 'Kinh doanh',
            'hopdong' => 'Hợp đồng',
            'ketoan' => 'Kế toán',
            'trienkhai' => 'Triển khai',
            'theodoi' => 'Người theo dõi',
        ];
    }

    protected function loadStatuses(): array
    {
        try {
            if (Schema::hasTable('contract_statuses')) {
                return ContractStatus::pluck('name', 'code')->toArray();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error loading statuses: " . $e->getMessage());
        }

        return [
            'choduyet' => 'Chờ duyệt',
            'daduyet' => 'Đã duyệt',
            'duthao' => 'Dự thảo',
            'thuongthao' => 'Thương thảo',
            'trinhky' => 'Trình ký',
            'daky' => 'Đã ký',
        ];
    }


    protected function hasNotification($role_code, $status_code)
    {
        $has = GeneralConfigurationNotification::whereHas('role', function ($query) use ($role_code) {
            $query->where('code', $role_code);
        })->whereHas('contractStatus', function ($query) use ($status_code) {
            $query->where('code', $status_code);
        })->value('enable');

        return $has;
    }

    protected function loadConfigurations(): array
    {
        $generalConfiguration = GeneralConfiguration::first();
        $hasQuanly = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'quanly');
        })->exists();
        $hasKinhdoanh = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'kinhdoanh');
        })->exists();
        $hasHopdong = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'hopdong');
        })->exists();
        $hasKetoan = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'ketoan');
        })->exists();
        $hasTrienkhai = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'trienkhai');
        })->exists();
        $hasTheodoi = GeneralConfigurationAlertTarget::whereHas('role', function ($query) {
            $query->where('code', 'theodoi');
        })->exists();

        $hasPhanQuyenTruyCap = GeneralConfigurationAutoUpdate::where('key', 'phan_quyen_truy_cap')->value('enable');
        $hasTinhTrangThanhLy = GeneralConfigurationAutoUpdate::where('key', 'tinh_trang_thanh_ly')->value('enable');
        $hasTinhTrangThanhToan = GeneralConfigurationAutoUpdate::where('key', 'tinh_trang_thanh_toan')->value('enable');
        $hasTinhTrangXuatHoaDon = GeneralConfigurationAutoUpdate::where('key', 'tinh_trang_xuat_hoa_don')->value('enable');

        $countNotification = GeneralConfigurationNotification::where('enable', 1)->count();


        $permissions = $this->loadPermissions();

        $partyAConfig = $this->loadPartyAConfiguration();

        return array_merge([
            'contract_expiry_notification' => $generalConfiguration->alert_anabled,
            'notification_days' => $generalConfiguration->alert_days_before,
            'notification_recipients' => [
                'quanly' => $hasQuanly,
                'kinhdoanh' => $hasKinhdoanh,
                'hopdong' => $hasHopdong,
                'ketoan' => $hasKetoan,
                'trienkhai' => $hasTrienkhai,
                'theodoi' => $hasTheodoi,
            ],
            'round_up_total' => $generalConfiguration->round_total,
            'phan_quyen_truy_cap' => $hasPhanQuyenTruyCap,
            'tinh_trang_thanh_ly' => $hasTinhTrangThanhLy,
            'tinh_trang_thanh_toan' => $hasTinhTrangThanhToan,
            'tinh_trang_xuat_hoa_don' => $hasTinhTrangXuatHoaDon,

            'status_change_notification' => ($countNotification > 0) ? true : false,
            'status_change_recipients' => [
                'choduyet' => [
                    'quanly' => $this->hasNotification('quanly', 'choduyet'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'choduyet'),
                    'hopdong' => $this->hasNotification('hopdong', 'choduyet'),
                    'ketoan' => $this->hasNotification('ketoan', 'choduyet'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'choduyet'),
                    'theodoi' => $this->hasNotification('theodoi', 'choduyet'),
                ],
                'daduyet' => [
                    'quanly' => $this->hasNotification('quanly', 'daduyet'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'daduyet'),
                    'hopdong' => $this->hasNotification('hopdong', 'daduyet'),
                    'ketoan' => $this->hasNotification('ketoan', 'daduyet'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'daduyet'),
                    'theodoi' => $this->hasNotification('theodoi', 'daduyet'),
                ],
                'duthao' => [
                    'quanly' => $this->hasNotification('quanly', 'duthao'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'duthao'),
                    'hopdong' => $this->hasNotification('hopdong', 'duthao'),
                    'ketoan' => $this->hasNotification('ketoan', 'duthao'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'duthao'),
                    'theodoi' => $this->hasNotification('theodoi', 'duthao'),
                ],
                'thuongthao' => [
                    'quanly' => $this->hasNotification('quanly', 'thuongthao'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'thuongthao'),
                    'hopdong' => $this->hasNotification('hopdong', 'thuongthao'),
                    'ketoan' => $this->hasNotification('ketoan', 'thuongthao'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'thuongthao'),
                    'theodoi' => $this->hasNotification('theodoi', 'thuongthao'),
                ],
                'trinhky' => [
                    'quanly' => $this->hasNotification('quanly', 'trinhky'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'trinhky'),
                    'hopdong' => $this->hasNotification('hopdong', 'trinhky'),
                    'ketoan' => $this->hasNotification('ketoan', 'trinhky'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'trinhky'),
                    'theodoi' => $this->hasNotification('theodoi', 'trinhky'),
                ],
                'daky' => [
                    'quanly' => $this->hasNotification('quanly', 'daky'),
                    'kinhdoanh' => $this->hasNotification('kinhdoanh', 'daky'),
                    'hopdong' => $this->hasNotification('hopdong', 'daky'),
                    'ketoan' => $this->hasNotification('ketoan', 'daky'),
                    'trienkhai' => $this->hasNotification('trienkhai', 'daky'),
                    'theodoi' => $this->hasNotification('theodoi', 'daky'),
                ],
            ],
            'permissions' => $permissions,

            'contract_type_supervisor_tab' => null,
            'role_supervisor_tab' => null,
            'user_supervisor_tab' => null,

            'template_name_export_tab' => '',
            'template_file_export_tab' => null,
            'contract_type_id_export_tab' => null,
            'description_export_tab' => '',
        ], $partyAConfig);
    }

    protected function hasPermission($role_code, $feature_code)
    {
        $has = Permission::whereHas('role', function ($query) use ($role_code) {
            $query->where('code', $role_code);
        })->whereHas('feature', function ($query) use ($feature_code) {
            $query->where('code', $feature_code);
        })->value('enable');

        return $has;
    }

    protected function loadPermissions(): array
    {
        $features = [
            'capnhatthongtin',
            'hanghoa',
            'xetduyet',
            'duthao',
            'thuongthao',
            'trinhky',
            'daky',
            'tinhtrangthuchien',
            'dieuchinh',
            'thanhly',
            'thanhtoan',
            'hoadon',
            'dinhkem',
            'ghichu',
            'xoa'
        ];

        $roles = [
            'quanly',
            'kinhdoanh',
            'hopdong',
            'ketoan',
            'trienkhai',
            'theodoi'
        ];

        $permissions = [];

        try {
            if (Schema::hasTable('permissions')) {
                foreach ($features as $feature) {
                    foreach ($roles as $role) {
                        $hasPermission = $this->hasPermission($role, $feature);
                        $permissions[$feature][$role] = $hasPermission ?? false;
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error loading permissions: " . $e->getMessage());
            foreach ($features as $feature) {
                foreach ($roles as $role) {
                    $permissions[$feature][$role] = ($role === 'quanly');
                }
            }
        }

        return $permissions;
    }

    public function loadPartyAConfiguration(): array
    {
        $partyA = ContractParticipant::where('party_type', 'A')->first();

        if (!$partyA) {
            return [
                'party_a_representative' => '',
                'party_a_position' => '',
                'party_a_tax_code' => '',
                'party_a_business_address' => '',
                'party_a_bank_account' => '',
                'party_a_bank' => '',
                'party_a_phone' => '',
                'party_a_email' => '',
            ];
        }

        return [
            'party_a_representative' => $partyA->full_name,
            'party_a_position' => $partyA->representative_position,
            'party_a_tax_code' => $partyA->tax_code,
            'party_a_business_address' => $partyA->address,
            'party_a_bank_account' => $partyA->bank_account,
            'party_a_bank' => $partyA->bank_name,
            'party_a_phone' => $partyA->phone,
            'party_a_email' => $partyA->email,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Cấu hình chung')
                    ->tabs([
                        Tab::make('Cấu hình')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('Cấu hình chung')
                                    ->schema([
                                        Checkbox::make('contract_expiry_notification')
                                            ->label('Cảnh báo ngày hết hiệu lực hợp đồng'),

                                        Grid::make()
                                            ->schema([
                                                TextInput::make('notification_days')
                                                    ->label('Cảnh báo trước:')
                                                    ->numeric()
                                                    ->suffix('ngày')
                                                    // ->visible(fn($get) => $get('contract_expiry_notification'))
                                                    ->columnSpan(1),
                                            ])->columns(6),
                                        // ->visible(fn($get) => $get('contract_expiry_notification')),

                                        Grid::make()
                                            ->schema([
                                                Checkbox::make('notification_recipients.quanly')
                                                    ->label('Quản lý')
                                                    ->columnSpan(1),
                                                Checkbox::make('notification_recipients.kinhdoanh')
                                                    ->label('Kinh doanh')
                                                    ->columnSpan(1),
                                                Checkbox::make('notification_recipients.hopdong')
                                                    ->label('Hợp đồng')
                                                    ->columnSpan(1),
                                                Checkbox::make('notification_recipients.ketoan')
                                                    ->label('Kế toán')
                                                    ->columnSpan(1),
                                                Checkbox::make('notification_recipients.trienkhai')
                                                    ->label('Triển khai')
                                                    ->columnSpan(1),
                                                Checkbox::make('notification_recipients.theodoi')
                                                    ->label('Người theo dõi')
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(6),
                                        // ->visible(fn($get) => $get('contract_expiry_notification')),

                                        Checkbox::make('round_up_total')
                                            ->label('Làm tròn đến hàng nghìn cột tổng cộng, tổng trong hàng hóa'),
                                    ]),

                                Section::make('Tự động cập nhật thông tin')
                                    ->schema([
                                        Checkbox::make('phan_quyen_truy_cap')
                                            ->label('Tự động phân quyền truy cập thông tin khách hàng cho người phụ trách, người theo dõi hợp đồng'),

                                        Checkbox::make('tinh_trang_thanh_ly')
                                            ->label('Tự động cập nhật tình trạng thanh lý, giá trị thanh lý (chỉ tự động cập nhật khi giá trị thanh toán >= giá trị hợp đồng)'),

                                        Checkbox::make('tinh_trang_thanh_toan')
                                            ->label('Tự động cập nhật tình trạng thanh toán theo thông tin Thanh toán'),

                                        Checkbox::make('tinh_trang_xuat_hoa_don')
                                            ->label('Tự động cập nhật tình trạng xuất hóa đơn theo thông tin Hóa đơn'),
                                    ]),

                                Section::make('Theo dõi hợp đồng')
                                    ->schema([
                                        $this->buildNotificationSection('status_change', 'Tự động thông báo khi chuyển trạng thái xử lý', $this->statusOptions),
                                    ]),
                            ]),

                        Tab::make('Phân quyền')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Phân quyền truy cập')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Placeholder::make('function_header')
                                                    ->label('Chức năng')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('admin_header')
                                                    ->label('Quản lý')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('business_header')
                                                    ->label('Kinh doanh')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('contract_header')
                                                    ->label('Hợp đồng')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('accounting_header')
                                                    ->label('Kế toán')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('deployment_header')
                                                    ->label('Triển khai')
                                                    ->columnSpan(1),
                                                \Filament\Forms\Components\Placeholder::make('follower_header')
                                                    ->label('Người theo dõi')
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(7),

                                        $this->createPermissionRow('capnhatthongtin', 'Cập nhật thông tin'),
                                        $this->createPermissionRow('hanghoa', 'Hàng hóa'),
                                        $this->createPermissionRow('xetduyet', 'Xét duyệt'),
                                        $this->createPermissionRow('duthao', 'Dự thảo'),
                                        $this->createPermissionRow('thuongthao', 'Thương thảo'),
                                        $this->createPermissionRow('trinhky', 'Trình ký'),
                                        $this->createPermissionRow('daky', 'Đã ký'),
                                        $this->createPermissionRow('tinhtrangthuchien', 'Tình trạng thực hiện'),
                                        $this->createPermissionRow('dieuchinh', 'Điều chỉnh'),
                                        $this->createPermissionRow('thanhly', 'Thanh lý'),
                                        $this->createPermissionRow('thanhtoan', 'Thanh toán'),
                                        $this->createPermissionRow('hoadon', 'Hóa đơn'),
                                        $this->createPermissionRow('dinhkem', 'Đính kèm'),
                                        $this->createPermissionRow('ghichu', 'Ghi chú'),
                                        $this->createPermissionRow('xoa', 'Xóa'),
                                    ])
                            ]),

                        Tab::make('Bên A')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Section::make('Đại diện bên A')
                                            ->schema([
                                                TextInput::make('party_a_representative')
                                                    ->label('Đại diện bên A')
                                                    ->placeholder('Đại diện bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_position')
                                                    ->label('Chức vụ đại diện bên A')
                                                    ->placeholder('Chức vụ đại diện bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_tax_code')
                                                    ->label('Mã số thuế bên A')
                                                    ->placeholder('Mã số thuế bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_business_address')
                                                    ->label('Địa chỉ đăng ký kinh doanh bên A')
                                                    ->placeholder('Địa chỉ đăng ký kinh doanh bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_bank_account')
                                                    ->label('Tài khoản ngân hàng bên A')
                                                    ->placeholder('Tài khoản ngân hàng bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_bank')
                                                    ->label('Ngân hàng bên A')
                                                    ->placeholder('Ngân hàng bên A')
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_phone')
                                                    ->label('Số điện thoại bên A')
                                                    ->placeholder('Số điện thoại bên A')
                                                    ->tel()
                                                    ->columnSpan('full'),

                                                TextInput::make('party_a_email')
                                                    ->label('Email bên A')
                                                    ->placeholder('Email bên A')
                                                    ->email()
                                                    ->columnSpan('full'),
                                            ])
                                            ->columnSpan(1)
                                    ])
                                    ->columns('full'),
                            ]),

                        Tab::make('Người phụ trách')
                            ->badge(Supervisor::count())
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Danh sách người phụ trách mặc định hiện tại')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.default-supervisors-table')
                                            ->viewData([
                                                'defaultSupervisors' => function () {
                                                    return Supervisor::query()
                                                        ->with(['contractType', 'role', 'user'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),

                                Section::make('Thêm người phụ trách, người theo dõi mặc định')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('contract_type_supervisor_tab')
                                                    ->label('Loại hợp đồng')
                                                    ->options(
                                                        ContractType::pluck('name', 'id')->toArray()
                                                    )
                                                    // ->required()
                                                    ->live()
                                                    ->searchable()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('role_supervisor_tab')
                                                    ->label('Vai trò')
                                                    ->options(
                                                        Role::pluck('name', 'id')->toArray()
                                                    )
                                                    // ->required()
                                                    ->live()
                                                    ->searchable()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('user_supervisor_tab')
                                                    ->label('Nhân viên')
                                                    ->options(
                                                        User::pluck('name', 'id')->toArray()
                                                    )
                                                    // ->required()
                                                    ->searchable()
                                                    ->live()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                    ]),

                            ]),

                        Tab::make('Mẫu xuất')
                            ->badge(ExportTemplate::count())
                            ->icon('heroicon-o-document')
                            ->schema([
                                Section::make('Danh sách mẫu xuất hiện tại')
                                    ->schema([
                                        \Filament\Forms\Components\View::make('components.export-templates-table')
                                            ->viewData([
                                                'exportTemplates' => function () {
                                                    return \App\Models\ExportTemplate::query()
                                                        ->with(['contractType'])
                                                        ->get();
                                                }
                                            ]),
                                        View::make('components.buttons.add-export-template-button'),
                                    ]),
                                Section::make('Thêm mẫu xuất')
                                    ->visible(fn(Get $get) => $get('show_add_export_template'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('template_name_export_tab')
                                                    ->label('Tên mẫu xuất')
                                                   
                                                    ->maxLength(255)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\FileUpload::make('template_file_export_tab')
                                                    ->label('Tải file mẫu (Chỉ hỗ trợ file Word: *.docx,*.docxm,*.dotx,*.dotm)')
                                                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-word.document.macroEnabled.12', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'application/vnd.ms-word.template.macroEnabled.12'])
                                                    ->disk('public')
                                                    ->preserveFilenames()
                                                    ->directory('export-templates')
                                                  
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('contract_type_id_export_tab')
                                                    ->label('Loại hợp đồng áp dụng')
                                                    ->options(
                                                        \App\Models\ContractType::pluck('name', 'id')->toArray()
                                                    )
                                                    ->searchable()
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),

                                        Grid::make()
                                            ->schema([
                                                \Filament\Forms\Components\Textarea::make('description_export_tab')
                                                    ->label('Mô tả')
                                                    ->maxLength(1000)
                                                    ->columnSpan(6),
                                            ])
                                            ->columns(6),
                                    ])
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function renderDefaultSupervisorsTable()
    {
        $defaultSupervisors = Supervisor::query()
            ->with(['contractType', 'role', 'user'])
            ->get();

        return view('components.default-supervisors-table', [
            'defaultSupervisors' => $defaultSupervisors
        ])->render();
    }


    protected function createPermissionRow(string $key, string $label, array $specialLabels = [])
    {
        return Grid::make()
            ->schema([
                \Filament\Forms\Components\Placeholder::make($key . '_label')
                    ->label($label)
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.quanly')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['quanly']) ? $specialLabels['quanly'] : '';
                    })
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.kinhdoanh')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['kinhdoanh']) ? $specialLabels['kinhdoanh'] : '';
                    })
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.hopdong')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['hopdong']) ? $specialLabels['hopdong'] : '';
                    })
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.ketoan')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['ketoan']) ? $specialLabels['ketoan'] : '';
                    })
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.trienkhai')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['trienkhai']) ? $specialLabels['trienkhai'] : '';
                    })
                    ->columnSpan(1),

                Checkbox::make('permissions.' . $key . '.theodoi')
                    ->label(function () use ($specialLabels) {
                        return isset($specialLabels['theodoi']) ? $specialLabels['theodoi'] : '';
                    })
                    ->columnSpan(1),
            ])
            ->columns(7)
            ->columnSpan('full');
    }

    protected function buildNotificationSection(string $key, string $label, array $statuses)
    {
        $schema = [
            Checkbox::make($key . '_notification')
                ->label($label),

            Grid::make()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('status_header')
                        ->label('Trạng thái')
                        // ->content('Trạng thái')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('admin_header')
                        ->label('Quản lý')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('business_header')
                        ->label('Kinh doanh')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('contract_header')
                        ->label('Hợp đồng')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('accounting_header')
                        ->label('Kế toán')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('deployment_header')
                        ->label('Triển khai')
                        ->columnSpan(1),
                    \Filament\Forms\Components\Placeholder::make('follower_header')
                        ->label('Người theo dõi')
                        ->columnSpan(1),
                ])
                ->columns(7)
            // ->visible(fn($get) => $get($key . '_notification'))
        ];

        foreach ($statuses as $statusKey => $statusLabel) {
            $schema[] = Grid::make()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make($statusKey . '_label')
                        ->label($statusLabel)
                        ->columnSpan(1),

                    Checkbox::make($key . '_recipients.' . $statusKey . '.quanly')
                        ->label('')
                        ->columnSpan(1),
                    Checkbox::make($key . '_recipients.' . $statusKey . '.kinhdoanh')
                        ->label('')
                        ->columnSpan(1),
                    Checkbox::make($key . '_recipients.' . $statusKey . '.hopdong')
                        ->label('')
                        ->columnSpan(1),
                    Checkbox::make($key . '_recipients.' . $statusKey . '.ketoan')
                        ->label('')
                        ->columnSpan(1),
                    Checkbox::make($key . '_recipients.' . $statusKey . '.trienkhai')
                        ->label('')
                        ->columnSpan(1),
                    Checkbox::make($key . '_recipients.' . $statusKey . '.theodoi')
                        ->label('')
                        ->columnSpan(1),
                ])
                ->columns(7)
                ->columnSpan('full')
                ->extraAttributes(['class' => 'status-row']);
            // ->visible(fn($get) => $get($key . '_notification'));
        }

        return Section::make($label)
            ->schema($schema)
            ->collapsible();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::beginTransaction();

        try {
            $this->saveGeneralConfigurationTab($data);
            $this->savePermissionTab($data);
            $this->savePartyATab($data);
            $this->saveSupervisor($data);
            $this->saveExportTemplate($data);

            DB::commit();

            Notification::make()
                ->title('Cấu hình đã được lưu thành công')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Có lỗi xảy ra')
                ->body($e->getMessage())
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error("Error saving configuration: " . $e->getMessage());
        }
    }

    protected function saveGeneralConfigurationTab(array $data): void
    {
        $generalConfig = GeneralConfiguration::firstOrCreate([]);
        $generalConfig->alert_anabled = $data['contract_expiry_notification'] ?? false;
        $generalConfig->alert_days_before = $data['notification_days'] ?? 0;
        $generalConfig->round_total = $data['round_up_total'] ?? false;
        $generalConfig->save();

        if (isset($data['notification_recipients'])) {
            GeneralConfigurationAlertTarget::query()->delete();

            foreach ($data['notification_recipients'] as $roleCode => $isSelected) {
                if ($isSelected) {
                    $role = Role::where('code', $roleCode)->first();
                    if ($role) {
                        GeneralConfigurationAlertTarget::create([
                            'role_id' => $role->id
                        ]);
                    }
                }
            }
        }


        $autoUpdateKeys = [
            'phan_quyen_truy_cap',
            'tinh_trang_thanh_ly',
            'tinh_trang_thanh_toan',
            'tinh_trang_xuat_hoa_don'
        ];

        foreach ($autoUpdateKeys as $key) {
            GeneralConfigurationAutoUpdate::updateOrCreate(
                ['key' => $key],
                ['enable' => $data[$key] ?? false]
            );
        }


        if (isset($data['status_change_notification']) && isset($data['status_change_recipients'])) {
            foreach ($data['status_change_recipients'] as $statusCode => $roles) {
                $contractStatus = ContractStatus::where('code', $statusCode)->first();
                if ($contractStatus) {
                    foreach ($roles as $roleCode => $isSelected) {
                        if ($isSelected) {
                            $role = Role::where('code', $roleCode)->first();
                            if ($role) {
                                GeneralConfigurationNotification::where('role_id', $role->id)
                                    ->where('status_id', $contractStatus->id)
                                    ->update(['enable' => $data['status_change_notification'] ?? false]);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function savePermissionTab(array $data): void
    {
        if (!isset($data['permissions'])) {
            return;
        }

        $features = [
            'capnhatthongtin',
            'hanghoa',
            'xetduyet',
            'duthao',
            'thuongthao',
            'trinhky',
            'daky',
            'tinhtrangthuchien',
            'dieuchinh',
            'thanhly',
            'thanhtoan',
            'hoadon',
            'dinhkem',
            'ghichu',
            'xoa'
        ];

        $roles = [
            'quanly',
            'kinhdoanh',
            'hopdong',
            'ketoan',
            'trienkhai',
            'theodoi'
        ];

        foreach ($features as $featureCode) {
            if (!isset($data['permissions'][$featureCode])) {
                continue;
            }

            $feature = Feature::where('code', $featureCode)->first();
            if ($feature) {
                foreach ($roles as $roleCode) {
                    $role = Role::where('code', $roleCode)->first();
                    if ($role) {
                        Permission::updateOrCreate([
                            'role_id' => $role->id,
                            'feature_id' => $feature->id
                        ], [
                            'enable' => $data['permissions'][$featureCode][$roleCode] ?? false
                        ]);
                    }
                }
            }
        }
    }

    protected function savePartyATab(array $data): void
    {
        $partyA = ContractParticipant::where('party_type', 'A')->first();

        if (!$partyA) {
            $partyA = new ContractParticipant();
            $partyA->party_type = 'A';
        }

        $partyA->full_name = $data['party_a_representative'] ?? '';
        $partyA->representative_position = $data['party_a_position'] ?? '';
        $partyA->tax_code = $data['party_a_tax_code'] ?? '';
        $partyA->address = $data['party_a_business_address'] ?? '';
        $partyA->bank_account = $data['party_a_bank_account'] ?? '';
        $partyA->bank_name = $data['party_a_bank'] ?? '';
        $partyA->phone = $data['party_a_phone'] ?? '';
        $partyA->email = $data['party_a_email'] ?? '';

        $partyA->save();
    }

    protected function saveSupervisor(array $data)
    {
        if (empty($data['contract_type_supervisor_tab']) || empty($data['role_supervisor_tab']) || empty($data['user_supervisor_tab'])) {
            return;
        }

        $exists = Supervisor::where('contract_type_id', $data['contract_type_supervisor_tab'])
            ->where('role_id', $data['role_supervisor_tab'])
            ->where('user_id', $data['user_supervisor_tab'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'user_supervisor_tab' => 'Người phụ trách mặc định này đã tồn tại.',
            ]);
            return;
        }

        Supervisor::create([
            'contract_type_id' => $data['contract_type_supervisor_tab'],
            'role_id' => $data['role_supervisor_tab'],
            'user_id' => $data['user_supervisor_tab'],
        ]);
        // $this->reset(['contract_type_supervisor_tab', 'role_supervisor_tab', 'user_supervisor_tab']);

        $this->dispatch('refresh');
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id): void
    {
        $supervisor = \App\Models\Supervisor::find($id);

        if (!$supervisor) {
            Notification::make()
                ->title('Không tìm thấy người phụ trách')
                ->danger()
                ->send();
            return;
        }

        $supervisor->delete();

        Notification::make()
            ->title('Xóa người phụ trách thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    public function saveExportTemplate(array $data): void
    {
        if (empty($data['template_file_export_tab'])) {
            return;
        }

        $file = $data['template_file_export_tab'];

        if (is_string($file)) {
            $filePath = $file;
        } else {
            $originalName = $file->getClientOriginalName();
            $filePath = $file->storeAs('export-templates', $originalName, 'public');
        }

        \App\Models\ExportTemplate::create([
            'name' => $data['template_name_export_tab'],
            'file_path' => $filePath,
            'contract_type_id' => $data['contract_type_id_export_tab'],
            'description' => $data['description_export_tab'] ?? null,
        ]);

        $this->form->fill([
            'template_name_export_tab' => '',
            'template_file_export_tab' => null,
            'contract_type_id_export_tab' => null,
            'description_export_tab' => '',
        ]);

        Notification::make()
            ->title('Thêm mẫu xuất thành công')
            ->success()
            ->send();
    }

    #[On('deleteTemplateConfirmed')]
    public function deleteTemplateConfirmed($id): void
    {
        $template = \App\Models\ExportTemplate::find($id);

        if (!$template) {
            Notification::make()
                ->title('Không tìm thấy mẫu xuất')
                ->danger()
                ->send();
            return;
        }

        if (Storage::exists($template->file_path)) {
            Storage::delete($template->file_path);
        }

        $template->delete();

        Notification::make()
            ->title('Xóa mẫu xuất thành công')
            ->success()
            ->send();

        $this->dispatch('refresh');
    }
}
