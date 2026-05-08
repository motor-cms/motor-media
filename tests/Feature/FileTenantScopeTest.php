<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Scopes\ClientScope;
use Motor\Media\Models\File;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));
afterEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));

describe('File tenant scope', function () {

    it('hides foreign-client files from a single-client user', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($clientA->id);

        File::create(['description' => 'Mine', 'author' => 'a', 'source' => 's', 'alt_text' => '', 'client_id' => $clientA->id]);
        File::create(['description' => 'Foreign', 'author' => 'a', 'source' => 's', 'alt_text' => '', 'client_id' => $clientB->id]);

        actingAsClientScopedUser($user);

        $titles = File::pluck('description')->all();
        expect($titles)->toContain('Mine');
        expect($titles)->not->toContain('Foreign');
    });

    it('lets SuperAdmin see all files', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        File::create(['description' => 'a', 'author' => 'a', 'source' => 's', 'alt_text' => '', 'client_id' => $clientA->id]);
        File::create(['description' => 'b', 'author' => 'a', 'source' => 's', 'alt_text' => '', 'client_id' => $clientB->id]);

        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');
        actingAsClientScopedUser($admin);

        expect(File::count())->toBeGreaterThanOrEqual(2);
    });

    it('auto-fills client_id on create for a single-client user', function () {
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($client->id);

        actingAsClientScopedUser($user);

        $file = File::create(['description' => 'auto', 'author' => 'a', 'source' => 's', 'alt_text' => '']);

        expect($file->client_id)->toBe($client->id);
    });
});
