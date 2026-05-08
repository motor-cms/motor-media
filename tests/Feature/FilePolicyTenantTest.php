<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Scopes\ClientScope;
use Motor\Media\Models\File;
use Motor\Media\Policies\FilePolicy;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));
afterEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));

function makeFileFor(int $clientId, string $tag = 'f'): File
{
    return File::create([
        'client_id'   => $clientId,
        'description' => 'File '.$tag,
        'author'      => 'tester',
        'source'      => 'https://example.test/'.$tag,
        'alt_text'    => 'alt',
        'is_global'   => false,
    ]);
}

function makeFileEditor(): User
{
    $user = User::factory()->create();
    $user->assignRole('Editor');
    $user->givePermissionTo(['files.read', 'files.write', 'files.delete']);

    return $user;
}

describe('FilePolicy tenant guard', function () {

    it('does not interfere with V1 callers (resolver unbound)', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $user = makeFileEditor();
        $user->clients()->attach($clientA->id);

        $foreign = makeFileFor($clientB->id, 'v1foreign');
        $policy = new FilePolicy;

        expect($policy->view($user, $foreign))->toBeTrue()
            ->and($policy->update($user, $foreign))->toBeTrue()
            ->and($policy->delete($user, $foreign))->toBeTrue();
    });

    it('denies per-instance abilities on foreign-client files under V2', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $user = makeFileEditor();
        $user->clients()->attach($clientA->id);

        $foreign = makeFileFor($clientB->id, 'foreign');
        $own = makeFileFor($clientA->id, 'own');

        actingAsClientScopedUser($user);
        $policy = new FilePolicy;

        expect($policy->view($user, $foreign))->toBeFalse()
            ->and($policy->update($user, $foreign))->toBeFalse()
            ->and($policy->delete($user, $foreign))->toBeFalse();

        expect($policy->view($user, $own))->toBeTrue()
            ->and($policy->update($user, $own))->toBeTrue();
    });

    it('denies for a user with empty client pivot', function () {
        $clientB = Client::factory()->create();
        $user = makeFileEditor();

        $file = makeFileFor($clientB->id, 'unreachable');

        actingAsClientScopedUser($user);
        $policy = new FilePolicy;

        expect($policy->view($user, $file))->toBeFalse()
            ->and($policy->update($user, $file))->toBeFalse();
    });

    it('lets SuperAdmin through via before() regardless of client', function () {
        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');
        $policy = new FilePolicy;

        expect($policy->before($admin, 'view'))->toBeTrue()
            ->and($policy->before($admin, 'update'))->toBeTrue();
    });
});
