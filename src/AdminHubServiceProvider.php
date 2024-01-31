<?php

namespace Lunar\Hub;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Hub\Auth\Manifest;
use Lunar\Hub\Base\ActivityLog\Manifest as ActivityLogManifest;
use Lunar\Hub\Base\DiscountTypesInterface;
use Lunar\Hub\Console\Commands\InstallHub;
use Lunar\Hub\Console\Commands\InstallPermissions;
use Lunar\Hub\Database\State\EnsurePermissionsAreUpgraded;
use Lunar\Hub\Editing\DiscountTypes;
use Lunar\Hub\Facades\ActivityLog;
use Lunar\Hub\Http\Livewire\Components\Account;
use Lunar\Hub\Http\Livewire\Components\ActivityLogFeed;
use Lunar\Hub\Http\Livewire\Components\Authentication\LoginForm;
use Lunar\Hub\Http\Livewire\Components\Authentication\PasswordReset;
use Lunar\Hub\Http\Livewire\Components\Avatar;
use Lunar\Hub\Http\Livewire\Components\Brands\BrandShow;
use Lunar\Hub\Http\Livewire\Components\Brands\BrandsIndex;
use Lunar\Hub\Http\Livewire\Components\Brands\BrandsTable;
use Lunar\Hub\Http\Livewire\Components\BrandSearch;
use Lunar\Hub\Http\Livewire\Components\Collections\CollectionGroupShow;
use Lunar\Hub\Http\Livewire\Components\Collections\CollectionGroupsIndex;
use Lunar\Hub\Http\Livewire\Components\Collections\CollectionShow;
use Lunar\Hub\Http\Livewire\Components\Collections\CollectionTree;
use Lunar\Hub\Http\Livewire\Components\Collections\CollectionTreeSelect;
use Lunar\Hub\Http\Livewire\Components\Collections\SideMenu;
use Lunar\Hub\Http\Livewire\Components\CollectionSearch;
use Lunar\Hub\Http\Livewire\Components\CurrentStaffName;
use Lunar\Hub\Http\Livewire\Components\Customers\CustomerShow;
use Lunar\Hub\Http\Livewire\Components\Customers\CustomersIndex;
use Lunar\Hub\Http\Livewire\Components\Customers\CustomersTable;
use Lunar\Hub\Http\Livewire\Components\Dashboard\SalesPerformance;
use Lunar\Hub\Http\Livewire\Components\Discounts\DiscountCreate;
use Lunar\Hub\Http\Livewire\Components\Discounts\DiscountShow;
use Lunar\Hub\Http\Livewire\Components\Discounts\DiscountsIndex;
use Lunar\Hub\Http\Livewire\Components\Discounts\DiscountsTable;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\AmountOff;
use Lunar\Hub\Http\Livewire\Components\Discounts\Types\BuyXGetY;
use Lunar\Hub\Http\Livewire\Components\FieldTypes\FileFieldtype;
use Lunar\Hub\Http\Livewire\Components\Orders\EmailNotification;
use Lunar\Hub\Http\Livewire\Components\Orders\OrderCapture;
use Lunar\Hub\Http\Livewire\Components\Orders\OrderRefund;
use Lunar\Hub\Http\Livewire\Components\Orders\OrderShow;
use Lunar\Hub\Http\Livewire\Components\Orders\OrdersIndex;
use Lunar\Hub\Http\Livewire\Components\Orders\OrdersTable;
use Lunar\Hub\Http\Livewire\Components\Orders\OrderStatus;
use Lunar\Hub\Http\Livewire\Components\ProductOptions\OptionManager;
use Lunar\Hub\Http\Livewire\Components\ProductOptions\OptionValueCreateModal;
use Lunar\Hub\Http\Livewire\Components\Products\Editing\CustomerGroups;
use Lunar\Hub\Http\Livewire\Components\Products\Options\OptionCreator;
use Lunar\Hub\Http\Livewire\Components\Products\Options\OptionSelector;
use Lunar\Hub\Http\Livewire\Components\Products\ProductCreate;
use Lunar\Hub\Http\Livewire\Components\Products\ProductShow;
use Lunar\Hub\Http\Livewire\Components\Products\ProductsIndex;
use Lunar\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeCreate;
use Lunar\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeShow;
use Lunar\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypesIndex;
use Lunar\Hub\Http\Livewire\Components\Products\Tables\ProductsTable;
use Lunar\Hub\Http\Livewire\Components\Products\Tables\ProductTypesTable;
use Lunar\Hub\Http\Livewire\Components\Products\Tables\ProductVariantsTable;
use Lunar\Hub\Http\Livewire\Components\Products\Variants\Editing\Inventory;
use Lunar\Hub\Http\Livewire\Components\Products\Variants\VariantShow;
use Lunar\Hub\Http\Livewire\Components\Products\Variants\VariantSideMenu;
use Lunar\Hub\Http\Livewire\Components\ProductSearch;
use Lunar\Hub\Http\Livewire\Components\ProductVariantSearch;
use Lunar\Hub\Http\Livewire\Components\Reporting\ApexChart;
use Lunar\Hub\Http\Livewire\Components\Settings\ActivityLog\ActivityLogIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Addons\AddonShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Addons\AddonsIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Attributes\AttributeEdit;
use Lunar\Hub\Http\Livewire\Components\Settings\Attributes\AttributeGroupEdit;
use Lunar\Hub\Http\Livewire\Components\Settings\Attributes\AttributeShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Attributes\AttributesIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Channels\ChannelCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\Channels\ChannelShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Channels\ChannelsIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Currencies\CurrenciesIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Currencies\CurrencyCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\Currencies\CurrencyShow;
use Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups\CustomerGroupCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups\CustomerGroupShow;
use Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups\CustomerGroupsIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Languages\LanguageCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\Languages\LanguageShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Languages\LanguagesIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Product\Options\OptionEdit;
use Lunar\Hub\Http\Livewire\Components\Settings\Product\Options\OptionsIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Product\Options\OptionValueEdit;
use Lunar\Hub\Http\Livewire\Components\Settings\Staff\StaffCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\Staff\StaffIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Staff\StaffShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\ActivityLogTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\AddonsTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\AttributesTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\ChannelsTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\CurrenciesTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\CustomerGroupsTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\LanguagesTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\StaffTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\TagsTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tables\TaxZonesTable;
use Lunar\Hub\Http\Livewire\Components\Settings\Tags\TagShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Tags\TagsIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Taxes\TaxClassesIndex;
use Lunar\Hub\Http\Livewire\Components\Settings\Taxes\TaxZoneCreate;
use Lunar\Hub\Http\Livewire\Components\Settings\Taxes\TaxZoneShow;
use Lunar\Hub\Http\Livewire\Components\Settings\Taxes\TaxZonesIndex;
use Lunar\Hub\Http\Livewire\Components\Tables\Actions\UpdateStatus;
use Lunar\Hub\Http\Livewire\Components\Tags;
use Lunar\Hub\Http\Livewire\Dashboard;
use Lunar\Hub\Http\Middleware\Authenticate;
use Lunar\Hub\Http\Middleware\RedirectIfAuthenticated;
use Lunar\Hub\Listeners\SetStaffAuthMiddlewareListener;
use Lunar\Hub\Menu\MenuRegistry;
use Lunar\Hub\Menu\OrderActionsMenu;
use Lunar\Hub\Menu\SettingsMenu;
use Lunar\Hub\Menu\SidebarMenu;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tables\Builders\CustomersTableBuilder;
use Lunar\Hub\Tables\Builders\OrdersTableBuilder;
use Lunar\Hub\Tables\Builders\ProductsTableBuilder;
use Lunar\Hub\Tables\Builders\ProductTypesTableBuilder;
use Lunar\Hub\Tables\Builders\ProductVariantsTableBuilder;
use Lunar\Models\Product;

class AdminHubServiceProvider extends ServiceProvider
{
    protected $configFiles = [
        'customers',
        'database',
        'products',
        'storefront',
        'system',
    ];

    protected $root = __DIR__.'/..';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "lunar-hub.$config");
        });

        $this->app->singleton(Manifest::class, function () {
            return new Manifest();
        });

        $this->app->singleton(MenuRegistry::class, function () {
            return new MenuRegistry();
        });

        $this->app->singleton(DiscountTypesInterface::class, function () {
            return new DiscountTypes();
        });

        $this->app->singleton(\Lunar\Hub\Editing\ProductSection::class, function ($app) {
            return new \Lunar\Hub\Editing\ProductSection();
        });

        $this->app->singleton(ActivityLog::class, function () {
            return new ActivityLogManifest();
        });

        $tableBuilders = [
            CustomersTableBuilder::class,
            OrdersTableBuilder::class,
            ProductsTableBuilder::class,
            ProductTypesTableBuilder::class,
            ProductVariantsTableBuilder::class,
        ];

        foreach ($tableBuilders as $tableBuilder) {
            $this->app->singleton($tableBuilder, function ($app) use ($tableBuilder) {
                return new $tableBuilder;
            });
        }
    }

    /**
     * Boot up the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if (config('lunar-hub.system.enable', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'adminhub');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'adminhub');

        if (! config('lunar-hub.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        Config::set('livewire-tables.translate_namespace', 'adminhub');

        $this->registerLivewireComponents();
        $this->registerAuthGuard();
        $this->registerPermissionManifest();
        $this->registerPublishables();
        $this->registerStateListeners();

        Route::bind('product', function (mixed $value, \Illuminate\Routing\Route $route) {
            if (in_array(\Lunar\Hub\Http\Middleware\Authenticate::class, $route->middleware())) {
                return Product::withTrashed()->findOrFail($value);
            }

            return $value;
        });

        // Commands
        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar-hub/$config.php"),
                ], 'lunar');
            });

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'lunar.migrations');

            $this->publishes([
                __DIR__.'/../resources/views/components/branding' => resource_path('views/vendor/adminhub/components/branding'),
                __DIR__.'/../resources/views/pdf' => resource_path('views/vendor/adminhub/pdf'),
            ], 'lunar.hub.views');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/adminhub'),
            ], 'lunar.hub.translations');

            $this->commands([
                InstallHub::class,
                InstallPermissions::class,
            ]);
        }

        // Menu Builder
        $this->registerMenuBuilder();

        Event::listen(
            RouteMatched::class,
            [SetStaffAuthMiddlewareListener::class, 'handle']
        );
    }

    protected function registerMenuBuilder()
    {
        SidebarMenu::make();
        SettingsMenu::make();
        OrderActionsMenu::make();
    }

    /**
     * Register the hub's Livewire components.
     *
     * @return void
     */
    protected function registerLivewireComponents()
    {
        $this->registerGlobalComponents();
        $this->registerAuthenticationComponents();
        $this->registerProductComponents();
        $this->registerBrandComponents();
        $this->registerCollectionComponents();
        $this->registerReportingComponents();
        $this->registerSettingsComponents();
        $this->registerOrderComponents();
        $this->registerCustomerComponents();
        $this->registerFieldtypeComponents();
        $this->registerDiscountComponents();
        $this->registerDashboardComponents();

        // Blade Components
        Blade::componentNamespace('Lunar\\Hub\\Views\\Components', 'hub');
    }

    /**
     * Register global components.
     *
     * @return void
     */
    protected function registerGlobalComponents()
    {
        Livewire::component('dashboard', Dashboard::class);
        Livewire::component('hub.components.activity-log-feed', ActivityLogFeed::class);
        Livewire::component('hub.components.product-search', ProductSearch::class);
        Livewire::component('hub.components.product-variant-search', ProductVariantSearch::class);
        Livewire::component('hub.components.collection-search', CollectionSearch::class);
        Livewire::component('hub.components.brand-search', BrandSearch::class);
        Livewire::component('hub.components.account', Account::class);
        Livewire::component('hub.components.avatar', Avatar::class);
        Livewire::component('hub.components.current-staff-name', CurrentStaffName::class);

        Livewire::component('hub.components.tags', Tags::class);
    }

    protected function registerDashboardComponents()
    {
        Livewire::component('hub.components.dashboard.sales-performance', SalesPerformance::class);
    }

    /**
     * Register the components used in the auth area.
     *
     * @return void
     */
    protected function registerAuthenticationComponents()
    {
        Livewire::component('hub.components.password-reset', PasswordReset::class);
        Livewire::component('hub.components.login-form', LoginForm::class);
    }

    protected function registerOrderComponents()
    {
        Livewire::component('hub.components.orders.index', OrdersIndex::class);
        Livewire::component('hub.components.orders.show', OrderShow::class);
        Livewire::component('hub.components.orders.refund', OrderRefund::class);
        Livewire::component('hub.components.orders.capture', OrderCapture::class);
        Livewire::component('hub.components.orders.status', OrderStatus::class);
        Livewire::component('hub.components.tables.actions.update-status', UpdateStatus::class);
        Livewire::component('hub.components.orders.emil-notification', EmailNotification::class);

        Livewire::component('hub.components.orders.table', OrdersTable::class);
    }

    protected function registerCustomerComponents()
    {
        Livewire::component('hub.components.customers.index', CustomersIndex::class);
        Livewire::component('hub.components.customers.table', CustomersTable::class);
        Livewire::component('hub.components.customers.show', CustomerShow::class);
    }

    /**
     * Register the components used in the products area.
     *
     * @return void
     */
    protected function registerProductComponents()
    {
        Livewire::component('hub.components.products.index', ProductsIndex::class);
        Livewire::component('hub.components.products.table', ProductsTable::class);
        Livewire::component('hub.components.products.show', ProductShow::class);
        Livewire::component('hub.components.products.create', ProductCreate::class);

        Livewire::component('hub.components.products.product-types.index', ProductTypesIndex::class);
        Livewire::component('hub.components.products.product-types.show', ProductTypeShow::class);
        Livewire::component('hub.components.products.product-types.create', ProductTypeCreate::class);
        Livewire::component('hub.components.products.product-types.table', ProductTypesTable::class);

        Livewire::component('hub.components.products.editing.customer-groups', CustomerGroups::class);

        Livewire::component('hub.components.products.options.option-creator', OptionCreator::class);
        Livewire::component('hub.components.products.options.option-selector', OptionSelector::class);

        Livewire::component('hub.components.products.variants.side-menu', VariantSideMenu::class);
        Livewire::component('hub.components.products.variants.show', VariantShow::class);
        Livewire::component('hub.components.products.variants.table', ProductVariantsTable::class);
        Livewire::component('hub.components.products.variants.editing.inventory', Inventory::class);

        Livewire::component('hub.components.product-options.option-manager', OptionManager::class);
        Livewire::component('hub.components.product-options.option-value-create-modal', OptionValueCreateModal::class);
    }

    /**
     * Register the components used in the brands area.
     *
     * @return void
     */
    protected function registerBrandComponents()
    {
        Livewire::component('hub.components.brands.index', BrandsIndex::class);
        Livewire::component('hub.components.brands.table', BrandsTable::class);
        Livewire::component('hub.components.brands.show', BrandShow::class);
    }

    /**
     * Register the components used in the collections area.
     *
     * @return void
     */
    protected function registerCollectionComponents()
    {
        Livewire::component('hub.components.collections.sidemenu', SideMenu::class);
        Livewire::component('hub.components.collections.collection-groups.index', CollectionGroupsIndex::class);
        Livewire::component('hub.components.collections.collection-groups.show', CollectionGroupShow::class);
        Livewire::component('hub.components.collections.show', CollectionShow::class);
        Livewire::component('hub.components.collections.collection-tree', CollectionTree::class);
        Livewire::component('hub.components.collections.collection-tree-select', CollectionTreeSelect::class);
    }

    /**
     * Register the components used in the reporting area.
     *
     * @return void
     */
    protected function registerReportingComponents()
    {
        Livewire::component('hub.components.reporting.apex-chart', ApexChart::class);
    }

    /**
     * Register the components used in the settings area.
     *
     * @return void
     */
    protected function registerSettingsComponents()
    {
        // Activity Log
        Livewire::component('hub.components.settings.activity-log.index', ActivityLogIndex::class);
        Livewire::component('hub.components.settings.activity-log.table', ActivityLogTable::class);

        // Attributes
        Livewire::component('hub.components.settings.attributes.index', AttributesIndex::class);
        Livewire::component('hub.components.settings.attributes.show', AttributeShow::class);
        Livewire::component('hub.components.settings.attributes.attribute-group-edit', AttributeGroupEdit::class);
        Livewire::component('hub.components.settings.attributes.attribute-edit', AttributeEdit::class);
        Livewire::component('hub.components.settings.attributes.table', AttributesTable::class);

        // Channels
        Livewire::component('hub.components.settings.channels.index', ChannelsIndex::class);
        Livewire::component('hub.components.settings.channels.table', ChannelsTable::class);
        Livewire::component('hub.components.settings.channels.show', ChannelShow::class);
        Livewire::component('hub.components.settings.channels.create', ChannelCreate::class);

        // Staff
        Livewire::component('hub.components.settings.staff.index', StaffIndex::class);
        Livewire::component('hub.components.settings.staff.table', StaffTable::class);
        Livewire::component('hub.components.settings.staff.show', StaffShow::class);
        Livewire::component('hub.components.settings.staff.create', StaffCreate::class);

        // Customer Groups
        Livewire::component('hub.components.settings.customer-groups.index', CustomerGroupsIndex::class);
        Livewire::component('hub.components.settings.customer-groups.show', CustomerGroupShow::class);
        Livewire::component('hub.components.settings.customer-groups.create', CustomerGroupCreate::class);
        Livewire::component('hub.components.settings.customer-groups.table', CustomerGroupsTable::class);

        // Languages
        Livewire::component('hub.components.settings.languages.index', LanguagesIndex::class);
        Livewire::component('hub.components.settings.languages.table', LanguagesTable::class);
        Livewire::component('hub.components.settings.languages.create', LanguageCreate::class);
        Livewire::component('hub.components.settings.languages.show', LanguageShow::class);

        // Tags
        Livewire::component('hub.components.settings.tags.index', TagsIndex::class);
        Livewire::component('hub.components.settings.tags.table', TagsTable::class);
        Livewire::component('hub.components.settings.tags.show', TagShow::class);

        // Currencies
        Livewire::component('hub.components.settings.currencies.index', CurrenciesIndex::class);
        Livewire::component('hub.components.settings.currencies.table', CurrenciesTable::class);
        Livewire::component('hub.components.settings.currencies.show', CurrencyShow::class);
        Livewire::component('hub.components.settings.currencies.create', CurrencyCreate::class);

        // Addons
        Livewire::component('hub.components.settings.addons.index', AddonsIndex::class);
        Livewire::component('hub.components.settings.addons.table', AddonsTable::class);
        Livewire::component('hub.components.settings.addons.show', AddonShow::class);

        // Product Options
        Livewire::component('hub.components.settings.product.options.index', OptionsIndex::class);
        Livewire::component('hub.components.settings.product.option-edit', OptionEdit::class);
        Livewire::component('hub.components.settings.product.option-value-edit', OptionValueEdit::class);

        // Taxes
        Livewire::component('hub.components.settings.taxes.tax-zones.index', TaxZonesIndex::class);
        Livewire::component('hub.components.settings.taxes.tax-zones.show', TaxZoneShow::class);
        Livewire::component('hub.components.settings.taxes.tax-zones.create', TaxZoneCreate::class);
        Livewire::component('hub.components.settings.taxes.tax-zones.table', TaxZonesTable::class);

        Livewire::component('hub.components.settings.taxes.tax-classes.index', TaxClassesIndex::class);
    }

    protected function registerFieldtypeComponents()
    {
        Livewire::component('hub.components.fieldtypes.file', FileFieldtype::class);
    }

    public function registerDiscountComponents()
    {
        Livewire::component('hub.components.discounts.index', DiscountsIndex::class);
        Livewire::component('hub.components.discounts.show', DiscountShow::class);
        Livewire::component('hub.components.discounts.create', DiscountCreate::class);
        Livewire::component('hub.components.discounts.table', DiscountsTable::class);

        Livewire::component('lunar.hub.http.livewire.components.discounts.types.amount-off', AmountOff::class);
        Livewire::component('lunar.hub.http.livewire.components.discounts.types.buy-x-get-y', BuyXGetY::class);
    }

    /**
     * Register our publishables.
     *
     * @return void
     */
    private function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/lunar/admin-hub/'),
        ], 'lunar.hub.public');
    }

    /**
     * Register our auth guard.
     *
     * @return void
     */
    protected function registerAuthGuard()
    {
        $this->app['config']->set('auth.providers.staff', [
            'driver' => 'eloquent',
            'model' => Staff::class,
        ]);

        $this->app['config']->set('auth.guards.staff', [
            'driver' => 'session',
            'provider' => 'staff',
        ]);

        Livewire::addPersistentMiddleware([
            Authenticate::class,
            RedirectIfAuthenticated::class,
        ]);
    }

    /**
     * Register our permissions manifest.
     *
     * @return void
     */
    protected function registerPermissionManifest()
    {
        Gate::after(function ($user, $ability) {
            // Are we trying to authorize something within the hub?
            $permission = $this->app->get(Manifest::class)->getPermissions()->first(fn ($permission) => $permission->handle === $ability);
            if ($permission) {
                return $user->admin || $user->hasPermissionTo($ability);
            }
        });
    }

    protected function registerStateListeners()
    {
        $states = [
            EnsurePermissionsAreUpgraded::class,
        ];

        foreach ($states as $state) {
            $class = new $state;

            Event::listen(
                [MigrationsStarted::class],
                [$class, 'prepare']
            );

            Event::listen(
                [MigrationsEnded::class, NoPendingMigrations::class],
                [$class, 'run']
            );
        }
    }
}
