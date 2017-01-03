<?php

namespace Xsanisty\Admin;

use Silex\Application;
use SilexStarter\Module\ModuleInfo;
use SilexStarter\Module\ModuleResource;
use SilexStarter\Module\ModuleProvider;

class DashboardModule extends ModuleProvider
{
    const INIT = 'dashboard.init';

    /**
     * {@inheritdoc}
     */
    public function __construct(Application $app)
    {
        $this->app  = $app;

        $this->info = new ModuleInfo(
            [
                'author_name'   => 'Xsanisty Development Team',
                'author_email'  => 'developers@xsanisty.com',
                'repository'    => 'https://github.com/xsanisty/SilexStarter-Dashboard',
                'name'          => 'SilexStarter Base Dashboard Module',
                'description'   => 'Provide basic dashboard page and login/logout function'
            ]
        );

        $this->resources = new ModuleResource(
            [
                'routes'        => 'Resources/routes.php',
                'middlewares'   => 'Resources/middlewares.php',
                'services'      => 'Resources/config/services.php',
                'views'         => 'Resources/views',
                'controllers'   => 'Controller',
                'config'        => 'Resources/config',
                'assets'        => 'Resources/assets',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleIdentifier()
    {
        return 'silexstarter-dashboard';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermissions()
    {
        return [
            'admin' => 'Administrator priviledge'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $self   = $this;
        $this->app['dispatcher']->addListener(
            DashboardModule::INIT,
            function () use ($self) {
                $menu       = $self->app['menu_manager']->create('admin_sidebar');
                $navbar     = $self->app['menu_manager']->create('admin_navbar');
                $breadcrumb = $self->app['menu_manager']->create('admin_breadcrumb');

                $template           = Config::get('@silexstarter-dashboard.config.template');
                $templateConf       = Config::get("@silexstarter-dashboard.config.templates.$template");

                $sidebarRenderer    = $templateConf['sidebar_renderer'];
                $navbarRenderer     = $templateConf['navbar_renderer'];
                $breadcrumbRenderer = $templateConf['breadcrumb_renderer'];

                $menu->setRenderer(
                    new $sidebarRenderer(
                        $self->app['asset_manager'],
                        $self->app['user'],
                        Config::get('@silexstarter-dashboard.config')
                    )
                );

                $navbar->setRenderer(
                    new $navbarRenderer(
                        $self->app['asset_manager'],
                        $self->app['user'],
                        Config::get('@silexstarter-dashboard.config')
                    )
                );

                $breadcrumb->setRenderer(
                    new $breadcrumbRenderer(
                        $self->app['asset_manager'],
                        $self->app['user'],
                        Config::get('@silexstarter-dashboard.config')
                    )
                );

                $breadcrumb->createItem(
                    'home',
                    [
                        'icon'  => 'dashboard',
                        'url'   => Url::to('admin.home'),
                        'label' => 'Dashboard'
                    ]
                );

                $self->registerNavbarMenu();
                $self->app['asset_manager']->exportVariable('base_url', Url::path('/', true));
                $self->app['asset_manager']->exportVariable('admin_template', $template);
                $self->app['asset_manager']->exportVariable('admin_skin', $templateConf['skin']);
            },
            5
        );
    }


    /**
     * Register menu item to navbar menu
     */
    protected function registerNavbarMenu()
    {
        $user   = $this->app['user'];
        $name   = $user ? $user->first_name.' '.$user->last_name : '';
        $email  = $user ? $user->email : '';
        $name   = trim($name) ? $name : $email;
        $icon   = $user->profile_pic
                ? $this->app['asset_manager']->resolvePath('img/profile/' . $user->profile_pic)
                : $this->app['asset_manager']->resolvePath('@silexstarter-dashboard/img/avatar.jpg');


        $menu   = $this->app['menu_manager']->get('admin_navbar')->createItem(
            'user',
            [
                'icon'  => 'user',
                'url'   => '#user',
                'meta'  => [
                    'renderer' => 'user-menu-renderer'
                ]
            ]
        );

        $menu->addChildren(
            'user-header',
            [
                'icon'  => $icon,
                'label' => $name,
                'meta'  => ['type' => 'header']

            ]
        );

        $menu->addChildren(
            'user-logout',
            [
                'label' => 'Logout',
                'icon'  => 'sign-out',
                'url'   => Url::to('admin.logout'),
                'meta'  => ['type' => 'link']
            ]
        );
    }
}
