<?php

namespace Mpociot\CaptainHook;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Mpociot\CaptainHook\Commands\AddWebhook;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Mpociot\CaptainHook\Commands\ListWebhooks;
use Mpociot\CaptainHook\Commands\DeleteWebhook;
use Mpociot\CaptainHook\Jobs\TriggerWebhooksJob;

/**
 * This file is part of CaptainHook arrrrr.
 *
 * @license MIT
 */
class CaptainHookServiceProvider extends ServiceProvider
{
    use DispatchesJobs;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners;

    /**
     * All registered webhooks.
     *
     * @var array
     */
    protected $webhooks = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->client = new Client();
        $this->cache = $this->app->make('Illuminate\Contracts\Cache\Repository');
        $this->config = $this->app->make('Illuminate\Contracts\Config\Repository');

        if ($this->app->runningInConsole()) {
            $this->publishMigration();
            $this->publishConfig();
            $this->publishSparkResources();
            $this->registerCommands();
        }

        $this->listeners = collect($this->config->get('captain_hook.listeners', []))->values();
        $this->registerEventListeners();
        $this->registerRoutes();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Publish migrations.
     *
     * @return void
     */
    protected function publishMigration()
    {
        $this->publishes([
            __DIR__.'/../../database/' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Publish configuration file.
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('captain_hook.php'),
        ], 'config');
    }

    protected function publishSparkResources()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views/', 'captainhook');

        $this->publishes([
            __DIR__.'/../../resources/js/' => base_path('resources/js/components/'),
            __DIR__.'/../../resources/views/' => base_path('resources/views/vendor/captainhook/settings/'),
        ], 'spark-resources');
    }

    /**
     * Register all active event listeners.
     */
    protected function registerEventListeners()
    {
        foreach ($this->listeners as $eventName) {
            $this->app['events']->listen($eventName . '*', [$this, 'handleEvent']);
        }
    }

    /**
     * @param array $listeners
     */
    public function setListeners($listeners)
    {
        $this->listeners = $listeners;

        $this->registerEventListeners();
    }

    /**
     * @param array $webhooks
     */
    public function setWebhooks($webhooks)
    {
        $this->webhooks = $webhooks;
        $this->getCache()->rememberForever(Webhook::CACHE_KEY, function () {
            return $this->webhooks;
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getWebhooks(): Collection
    {
        // Check if migration ran
        if (Schema::hasTable((new Webhook)->getTable())) {
            return collect($this->getCache()->rememberForever(Webhook::CACHE_KEY, function () {
                return Webhook::all();
            }));
        }

        return collect();
    }

    /**
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Event listener.
     *
     * @param $eventName
     * @param $eventData
     */
    public function handleEvent($eventName, $eventData)
    {
        $webhooks = $this->getWebhooks()->where('event', $eventName);
        $webhooks = $webhooks->filter($this->config->get('captain_hook.filter', null));

        if (! $webhooks->isEmpty()) {
            $this->dispatch(new TriggerWebhooksJob($webhooks, $eventData));
        }
    }

    /**
     * Register the artisan commands.
     */
    protected function registerCommands(): void
    {
        $this->commands(
            ListWebhooks::class,
            AddWebhook::class,
            DeleteWebhook::class,
        );
    }

    /**
     * Register predefined routes used for Spark.
     */
    protected function registerRoutes(): void
    {
        if (class_exists('Laravel\Spark\Providers\AppServiceProvider')) {
            include __DIR__.'/../../routes.php';
        }
    }
}
