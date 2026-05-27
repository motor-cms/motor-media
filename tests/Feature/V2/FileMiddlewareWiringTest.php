<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Scopes\ClientScope;
use Motor\Media\Models\File;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));
afterEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));

// Phase 9 follow-up: prove the V2 ScopeRequestsToClient middleware actually
// fires on motor-media's V2 route group.
it('binds the tenant resolver on motor-media V2 routes', function () {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();

    $user = User::factory()->create();
    $user->assignRole('Editor');
    $user->givePermissionTo(['files.read']);
    $user->clients()->attach($clientA->id);

    $foreign = File::withoutGlobalScopes()->create([
        'client_id'   => $clientB->id,
        'description' => 'foreign file',
        'author'      => 'mw-test',
        'source'      => 'https://example.test/mw',
        'alt_text'    => 'mw',
        'is_global'   => false,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/v2/files/'.$foreign->id);

    $response->assertNotFound();
});
