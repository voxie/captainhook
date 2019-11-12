<?php

namespace Mpociot\CaptainHook\Tests;

use Mockery;
use Mpociot\CaptainHook\Commands\AddWebhook;
use Mpociot\CaptainHook\Commands\DeleteWebhook;
use Mpociot\CaptainHook\Webhook;

class CommandsTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        //
    }

    public function testCannotAddWebhookWithoutName()
    {
        $cmd = Mockery::mock(AddWebhook::class . '[argument,error]');

        $cmd->shouldReceive('error')
            ->once()
            ->with(Mockery::type('string'));

        $cmd->shouldReceive('argument')
            ->twice();

        $cmd->shouldReceive('argument')
            ->with('url')
            ->andReturn('http://foo.bar');

        $cmd->handle();

        $this->assertTrue(true);
    }

    public function testCanAddWebhook()
    {
        $cmd = Mockery::mock(AddWebhook::class . '[argument,info]');

        $cmd->shouldReceive('argument')
            ->with('url')
            ->andReturn('http://foo.bar');

        $cmd->shouldReceive('argument')
            ->with('event')
            ->andReturn('TestModelTestModel');

        $cmd->shouldReceive('info')
            ->with(Mockery::type('string'));

        $cmd->handle();

        $this->seeInDatabase('webhooks', [
            'event' => 'TestModelTestModel',
            'url'   => 'http://foo.bar',
        ]);
    }

    public function testCannotDeleteWebhookWithWrongID()
    {
        Webhook::create([
            'url'   => 'http://foo.baz',
            'event' => 'DeleteWebhook',
        ]);
        $cmd = Mockery::mock(DeleteWebhook::class . '[argument,error]');

        $cmd->shouldReceive('argument')
            ->with('id')
            ->andReturn(null);

        $cmd->shouldReceive('error')
            ->with(Mockery::type('string'));

        $cmd->handle();

        $this->seeInDatabase('webhooks', [
            'url'   => 'http://foo.baz',
            'event' => 'DeleteWebhook',
        ]);
    }

    public function testCanDeleteWebhook()
    {
        $webhook = Webhook::create([
            'url'   => 'http://foo.baz',
            'event' => 'DeleteWebhook',
        ]);
        $cmd = Mockery::mock(DeleteWebhook::class . '[argument,info]');

        $cmd->shouldReceive('argument')
            ->with('id')
            ->andReturn($webhook->getKey());

        $cmd->shouldReceive('info')
            ->with(Mockery::type('string'));

        $cmd->handle();

        $this->notSeeInDatabase('webhooks', [
            'url'   => 'http://foo.baz',
            'event' => 'DeleteWebhook',
        ]);
    }
}
