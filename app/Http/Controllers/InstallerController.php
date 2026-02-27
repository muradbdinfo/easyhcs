<?php

namespace App\Http\Controllers;

use App\Services\InstallerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstallerController extends Controller
{
    public function __construct(private InstallerService $installer) {}

    public function index(): Response
    {
        return Inertia::render('install/InstallWizard');
    }

    public function checkRequirements(): JsonResponse
    {
        return response()->json($this->installer->checkRequirements());
    }

    public function testDatabase(Request $request): JsonResponse
    {
        $data = $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|numeric',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        return response()->json($this->installer->testDatabase($data));
    }

    public function run(Request $request): JsonResponse
    {
        $request->validate([
            'database.host'     => 'required|string',
            'database.port'     => 'required|numeric',
            'database.database' => 'required|string',
            'database.username' => 'required|string',
            'database.password' => 'nullable|string',

            'admin.name'                  => 'required|string|max:100',
            'admin.email'                 => 'required|email',
            'admin.password'              => 'required|string|min:8',
            'admin.password_confirmation' => 'required|same:admin.password',

            'app.name'     => 'required|string|max:100',
            'app.url'      => 'required|url',
            'app.timezone' => 'required|string',

            'mail.mailer'       => 'required|string',
            'mail.host'         => 'required|string',
            'mail.port'         => 'required|numeric',
            'mail.username'     => 'nullable|string',
            'mail.password'     => 'nullable|string',
            'mail.encryption'   => 'nullable|string',
            'mail.from_address' => 'required|email',
        ]);

        $result = $this->installer->install($request->all());

        return response()->json($result);
    }
}