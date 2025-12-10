<?php

namespace Motor\Media\Http\Controllers\Internal;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class MediaOpsController extends Controller
{
    public function check(Request $request, string $token): JsonResponse
    {
        if (! $this->validateToken($token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $exitCode = Artisan::call('motor-media:check', [
            '--headless' => true,
        ]);

        $output = Artisan::output();

        return response()->json([
            'success' => $exitCode === 0,
            'exit_code' => $exitCode,
            'output' => trim($output),
        ]);
    }

    public function sync(Request $request, string $token): JsonResponse
    {
        if (! $this->validateToken($token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $dryRun = $request->boolean('dry_run', false);

        $exitCode = Artisan::call('motor-media:sync', [
            '--headless' => true,
            '--dry-run' => $dryRun,
        ]);

        $output = Artisan::output();

        return response()->json([
            'success' => $exitCode === 0,
            'exit_code' => $exitCode,
            'output' => trim($output),
            'dry_run' => $dryRun,
        ]);
    }

    private function validateToken(string $token): bool
    {
        $expectedToken = config('motor-media.ops_token');

        if (empty($expectedToken)) {
            return false;
        }

        return hash_equals($expectedToken, $token);
    }
}
