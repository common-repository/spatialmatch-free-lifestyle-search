<?php
namespace SpatialMatchIdx\admin;

use SpatialMatchIdx\admin\actions\ajax\ContactFormSubmissionAction;
use SpatialMatchIdx\admin\actions\ajax\DeleteFavoritesAction;
use SpatialMatchIdx\admin\actions\ajax\DeleteLeadAction;
use SpatialMatchIdx\admin\actions\ajax\DeleteSearchesAction;
use SpatialMatchIdx\admin\actions\ajax\UpdateSearchesAlertAction;
use SpatialMatchIdx\admin\actions\ajax\UserRegistrationAction;
use SpatialMatchIdx\admin\actions\pages\help\HelpPageShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadAnalyticsShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadCreateAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadDetailShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadFavoritesShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadFormEntreiesShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadNewShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadSearchesShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\detail\LeadUpdateAction;
use SpatialMatchIdx\admin\actions\pages\leads\LeadsExportAction;
use SpatialMatchIdx\admin\actions\pages\leads\LeadsPageShowAction;
use SpatialMatchIdx\admin\actions\pages\leads\LeadsSearchAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\AgentLicenseRegistrationShowAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\DeveloperLicenseRegistrationShowAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\SuccessLicenseRegistrationShowAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\LicenseRegistrationSaveAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\TrialLicenseRegistrationShowAction;
use SpatialMatchIdx\admin\actions\pages\licenseRegistrations\UpgradeLicenseRegistrationShowAction;
use SpatialMatchIdx\admin\actions\pages\licenseTypes\LicenseTypesShowAction;
use SpatialMatchIdx\admin\actions\tabs\color\ColorResetDefaultAction;
use SpatialMatchIdx\admin\actions\tabs\color\ColorSaveAction;
use SpatialMatchIdx\admin\actions\tabs\color\ColorShowAction;
use SpatialMatchIdx\admin\actions\tabs\compliance\ComplianceShowAction;
use SpatialMatchIdx\admin\actions\tabs\compliance\ComplianceSaveAction;
use SpatialMatchIdx\admin\actions\tabs\general\GeneralSaveAction;
use SpatialMatchIdx\admin\actions\tabs\general\GeneralShowAction;
use SpatialMatchIdx\admin\actions\tabs\license\LicenseSaveAction;
use SpatialMatchIdx\admin\actions\tabs\license\LicenseShowAction;
use SpatialMatchIdx\admin\actions\tabs\map\MapSaveAction;
use SpatialMatchIdx\admin\actions\tabs\map\MapShowAction;
use SpatialMatchIdx\admin\sections\lead\LeadAnalyticsTab;
use SpatialMatchIdx\admin\sections\ComplianceSettings;
use SpatialMatchIdx\admin\sections\lead\LeadDetailTab;
use SpatialMatchIdx\admin\sections\lead\LeadFavoritesTab;
use SpatialMatchIdx\admin\sections\lead\LeadFormEntriesTab;
use SpatialMatchIdx\admin\sections\lead\LeadSearchesTab;
use SpatialMatchIdx\admin\sections\LeadTabsContainer;
use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\sections\ColorSettings;
use SpatialMatchIdx\admin\sections\GeneralSettings;
use SpatialMatchIdx\admin\sections\LicenseSettings;
use SpatialMatchIdx\admin\sections\MapSettings;
use SpatialMatchIdx\core\compliances\ComplianceManager;
use SpatialMatchIdx\core\routes\Router;
use SpatialMatchIdx\front\compliances\compliancesMarkets\NnerenCompliance;
use SpatialMatchIdx\front\compliances\compliancesMarkets\NwmlsCompliance;
use SpatialMatchIdx\front\compliances\compliancesMarkets\TexasCompliance;
use SpatialMatchIdx\models\LicenseModel;
use SpatialMatchIdx\services\AdminNoticesService;
use SpatialMatchIdx\services\LicenseService;
use SpatialMatchIdx\SpatialMatchIdx;


class PluginAdmin
{
    const STAGE_PLUGINS_LOADED = 'plugins_loaded';
    const STAGE_ADMIN_MENU = 'admin_menu';
    const SETTINGS_PAGE_SLUG = SpatialMatchIdx::SLUG;
    const LEADS_PAGE_SLUG = 'hji-spatialmatch-idx-leads';
    const HELP_PAGE_SLUG = 'hji-spatialmatch-idx-help';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $pagesHookSuffix = [];

    /**
     * @var string
     */
    private $adminPluginUrl;

    /**
     * @var array
     */
    private static $instances = [];

    public function load()
    {
        $activatePlugin = get_option('spm_plugin_activate', 0);
        if ($activatePlugin) {
            delete_option('spm_plugin_activate');
            $url = home_url('/wp-admin/admin.php?page=' . self::SETTINGS_PAGE_SLUG);
            wp_redirect($url, 303);
            exit();
        }

        $this->initSession();
        $this->router = new Router();
        $this->registerRoutes($this->router);
        $this->routing(self::STAGE_PLUGINS_LOADED);
        $this->initLicenseService();
        $this->hooks();
        $this->initLicenseService();
        $this->initNotices();


    }

    /**
     * @return PluginAdmin
     */
    public static function getInstance(): PluginAdmin
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function activatePlugin()
    {
        $licenseModelData =LicenseModel::getData();

        if (!empty($licenseModelData->getAttribute('license_key'))) {
            return;
        }

        $licenseModel = new LicenseModel();
        $licenseKey = $licenseModel->getAttribute('default_license_key');

        $postData = ['license_key' => $licenseKey];
        $licenseService = LicenseService::getInstance();

        $operationType = $licenseService->checkOperationType($postData);

        $licenseModel->setAttributes($postData);

        $licenseModel->save();

        $licenseService->afterLicenseKeySave($operationType);

        update_option('spm_plugin_activate', 1);
    }

    /**
     * @param $stage
     */
    private function routing($stage)
    {
        $action = $this->router->route($_REQUEST, $stage);

        if ($action !== null) {
            $actionObject = new $action();
            $actionObject->execute();
        }

        if (wp_doing_ajax() && isset($_REQUEST['action']) && ($action = $this->router->ajaxRoute($_REQUEST['action']))) {
            $actionObject = new $action();
            $actionObject->execute();
        }
    }

    /**
     * Init hooks
     *
     * @since 3.0.0
     */
    public function hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_menu', [ $this, 'addMenu' ]);
        add_action('wp_ajax_hj_spm_idx_export_leads', [new LeadsExportAction(), 'execute']);
    }

    /**
     * Init user session
     */
    public function initSession()
    {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * Register the styles for the admin area.
     *
     * @since 3.0.0
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueueStyles(string $hook_suffix)
    {
        if (false === strpos($hook_suffix, SpatialMatchIdx::SLUG)) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style(
            'spatialmatch-idx-settings',
            SPATIALMATCH_IDX_URL . 'assets/build/css/admin/settings.css',
            [],
            SpatialMatchIdx::VERSION,
            'all'
        );

        wp_enqueue_style(
            'select2',
            SPATIALMATCH_IDX_URL . 'assets/vendors/select2/select2.min.css',
            [],
            SpatialMatchIdx::VERSION,
            'all'
        );

        wp_enqueue_style(
            'multiple-emails-input',
            SPATIALMATCH_IDX_URL . 'assets/vendors/jquery-emailinput/jquery.emailinput.min.css',
            [],
            SpatialMatchIdx::VERSION,
            'all'
        );

        wp_enqueue_style('jquery-ui-core', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css');
        wp_enqueue_style('jquery-ui-themes', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/theme.min.css');
        wp_enqueue_style('jquery-ui-dialog');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 3.0.0
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueueScripts(string $hook_suffix)
    {
        if (false === strpos($hook_suffix, SpatialMatchIdx::SLUG)) {
            return;
        }

        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        wp_enqueue_script( 'wp-color-picker');
        wp_enqueue_script(
            'spatialmatch-idx-settings',
            SPATIALMATCH_IDX_URL . 'assets/build/js/admin/settings/app.js',
            ['jquery'],
            SpatialMatchIdx::VERSION,
            true
        );

        wp_enqueue_script(
            'select2',
            SPATIALMATCH_IDX_URL . 'assets/vendors/select2/select2.min.js',
            ['jquery'],
            SpatialMatchIdx::VERSION,
            true
        );

        wp_enqueue_script(
            'jquery.validate',
            SPATIALMATCH_IDX_URL . 'assets/vendors/validator/jquery.validate.min.js',
            ['jquery'],
            SpatialMatchIdx::VERSION,
            true
        );

        wp_enqueue_script(
            'multiple-emails-input',
            SPATIALMATCH_IDX_URL . 'assets/vendors/jquery-emailinput/jquery.emailinput.min.js',
            ['jquery'],
            SpatialMatchIdx::VERSION,
            true
        );

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-datepicker');
    }

    /**
     * Add plugin page in WordPress menu.
     *
     * @since 3.0.0
     */
    public function addMenu()
    {
        $menuNotice =  (LicenseService::getInstance()->hasMessages() && LicenseService::getInstance()->showMenuBadge())
            ? '<span class="awaiting-mod" style="right: 10px; position: absolute;"><span class="pending-count">' . count(LicenseService::getInstance()->getMessages()) . '</span></span>'
            : '';

        $this->pagesHookSuffix[] = add_menu_page(
            esc_html__('SpatialMatch IDX', 'hji-spatialmatch-idx'),
            esc_html__('SpatialMatch IDX', 'hji-spatialmatch-idx') . $menuNotice,
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            [
                $this,
                'createMainPluginPage',
            ],
            SPATIALMATCH_IDX_URL . '/assets/img/admin-menu.png'
        );

        $this->pagesHookSuffix[self::SETTINGS_PAGE_SLUG] = add_submenu_page(self::SETTINGS_PAGE_SLUG, __('Settings', 'hji-spatialmatch-idx'), esc_html__('Settings', 'hji-spatialmatch-idx'), 'manage_options', self::SETTINGS_PAGE_SLUG, [$this, 'createMainPluginPage']);

        $this->pagesHookSuffix[self::LEADS_PAGE_SLUG] = add_submenu_page(self::SETTINGS_PAGE_SLUG, __('Leads', 'hji-spatialmatch-idx'), esc_html__('Leads', 'hji-spatialmatch-idx'), 'manage_options', self::LEADS_PAGE_SLUG , [$this, 'createLeadsPage']);

        $this->pagesHookSuffix[self::HELP_PAGE_SLUG] = add_submenu_page(self::SETTINGS_PAGE_SLUG, __('Help', 'hji-spatialmatch-idx'), esc_html__('Help', 'hji-spatialmatch-idx'), 'manage_options', self::HELP_PAGE_SLUG , [$this, 'createHelpPage']);

        $this->adminPluginUrl = menu_page_url(self::SETTINGS_PAGE_SLUG, false);
    }

    /**
     * Options page callback
     */
    public function createMainPluginPage()
    {
        $this->registerCompliances();
        $this->registerSettingsTabs();

        $this->routing(self::STAGE_ADMIN_MENU);
    }

    public function createLeadsPage()
    {
        $this->registerLeadTabs();

        $this->routing(self::STAGE_ADMIN_MENU);
    }

    public function createHelpPage()
    {
        $this->routing(self::STAGE_ADMIN_MENU);
    }

    public function registerSettingsTabs()
    {
        $settingsTabs = SettingsTabsContainer::getInstance();
        $settingsTabs->addTab(new LicenseSettings());
        $settingsTabs->addTab(new GeneralSettings());
        $settingsTabs->addTab(new MapSettings());
        $settingsTabs->addTab(new ColorSettings());

        $licenseMarkets = LicenseService::getInstance()->getMarkets();
        if (is_array($licenseMarkets) && ComplianceManager::getInstance()->isMarketsComplianceBySlug(TexasCompliance::class, $licenseMarkets)) {
            $settingsTabs->addTab(new ComplianceSettings());
        }
    }

    public function registerLeadTabs()
    {
        $leadTabs = LeadTabsContainer::getInstance();
        $leadTabs->addTab(new LeadDetailTab());
        $leadTabs->addTab(new LeadFavoritesTab());
        $leadTabs->addTab(new LeadSearchesTab());
        $leadTabs->addTab(new LeadFormEntriesTab());
        $leadTabs->addTab(new LeadAnalyticsTab());
    }

    public function registerCompliances()
    {
        $compliancesManager = ComplianceManager::getInstance();
        $compliancesManager->addCompliance(new TexasCompliance());
        $compliancesManager->addCompliance(new NwmlsCompliance());
        $compliancesManager->addCompliance(new NnerenCompliance());
    }

    /**
     * @param Router $router
     */
    private function registerRoutes(Router $router)
    {
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'license', LicenseShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-license', LicenseSaveAction::class, self::STAGE_PLUGINS_LOADED);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'general', GeneralShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-general-settings', GeneralSaveAction::class, self::STAGE_PLUGINS_LOADED);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'map', MapShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-map-settings', MapSaveAction::class, self::STAGE_PLUGINS_LOADED);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'color', ColorShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-color-settings', ColorSaveAction::class, self::STAGE_PLUGINS_LOADED);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'reset-color-settings', ColorResetDefaultAction::class, self::STAGE_PLUGINS_LOADED);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'license-types', LicenseTypesShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::HELP_PAGE_SLUG, null, HelpPageShowAction::class, self::STAGE_ADMIN_MENU);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'trial-license-registration', TrialLicenseRegistrationShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'agent-license-registration', AgentLicenseRegistrationShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'developer-license-registration', DeveloperLicenseRegistrationShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'upgrade-license', UpgradeLicenseRegistrationShowAction::class, self::STAGE_ADMIN_MENU);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-license-registration', LicenseRegistrationSaveAction::class, self::STAGE_PLUGINS_LOADED);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'success-license-registration', SuccessLicenseRegistrationShowAction::class, self::STAGE_ADMIN_MENU);

        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'compliance', ComplianceShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::SETTINGS_PAGE_SLUG, 'save-compliance-settings', ComplianceSaveAction::class, self::STAGE_PLUGINS_LOADED);

        /* Leads Routes */
        $router->addRoute(self::LEADS_PAGE_SLUG, null, LeadsPageShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'search-leads', LeadsSearchAction::class, self::STAGE_PLUGINS_LOADED);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'export-leads', LeadsExportAction::class, self::STAGE_PLUGINS_LOADED);

        $router->addRoute(self::LEADS_PAGE_SLUG, 'lead-detail', LeadDetailShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'lead-favorites', LeadFavoritesShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'lead-searches', LeadSearchesShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'lead-form-entries', LeadFormEntreiesShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'new-lead', LeadNewShowAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'create-lead', LeadCreateAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'update-lead', LeadUpdateAction::class, self::STAGE_ADMIN_MENU);
        $router->addRoute(self::LEADS_PAGE_SLUG, 'lead-analytics', LeadAnalyticsShowAction::class, self::STAGE_ADMIN_MENU);

        /* AJAX Routes */
        $router->addAjaxRoute('handleContactFormSubmission', ContactFormSubmissionAction::class);
        $router->addAjaxRoute('handleUserRegistration', UserRegistrationAction::class);
        $router->addAjaxRoute('handleUpdateSearchesAlert', UpdateSearchesAlertAction::class);
        $router->addAjaxRoute('deleteLead', DeleteLeadAction::class);
        $router->addAjaxRoute('deleteSearches', DeleteSearchesAction::class);
        $router->addAjaxRoute('deleteFavorites', DeleteFavoritesAction::class);
    }

    private function initLicenseService()
    {
        $license = LicenseService::getInstance();
        $license->licenseNotices();
    }

    private function initNotices()
    {
        AdminNoticesService::getInstance()->showMessages();
    }
}
