<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Patient;
use App\Services\AuditService;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(private PatientService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage-patients');

        $patients = $this->service->list($request->only(['search', 'gender', 'blood_group', 'patient_type', 'per_page']));
        return response()->json(compact('patients'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage-patients');

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'name_bn'        => ['nullable', 'string', 'max:150'],
            'gender'         => ['required', 'in:male,female,other'],
            'date_of_birth'  => ['nullable', 'date', 'before:today'],
            'blood_group'    => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'address'        => ['nullable', 'string'],
            'nid'            => ['nullable', 'string', 'max:20'],
            'guardian_name'  => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'patient_type'   => ['required', 'in:regular,walkin'],
            'notes'          => ['nullable', 'string'],
        ]);

        $patient = $this->service->create($data);
        AuditService::log('created', $patient, null, $patient->only(['patient_no', 'name']), "Patient '{$patient->name}' registered");

        return response()->json(['message' => 'Patient registered successfully.', 'patient' => $patient], 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        $this->authorize('manage-patients');
        return response()->json(['patient' => $patient->append('age')]);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('manage-patients');

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'name_bn'        => ['nullable', 'string', 'max:150'],
            'gender'         => ['required', 'in:male,female,other'],
            'date_of_birth'  => ['nullable', 'date', 'before:today'],
            'blood_group'    => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'address'        => ['nullable', 'string'],
            'nid'            => ['nullable', 'string', 'max:20'],
            'guardian_name'  => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'patient_type'   => ['required', 'in:regular,walkin'],
            'notes'          => ['nullable', 'string'],
        ]);

        $old     = $patient->toArray();
        $updated = $this->service->update($patient, $data);
        AuditService::log('updated', $updated, $old, $updated->toArray(), "Patient '{$updated->name}' updated");

        return response()->json(['message' => 'Patient updated successfully.', 'patient' => $updated]);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $this->authorize('manage-patients');

        AuditService::log('deleted', $patient, $patient->only(['patient_no', 'name']), null, "Patient '{$patient->name}' deleted");
        $this->service->delete($patient);

        return response()->json(['message' => 'Patient deleted successfully.']);
    }

    public function search(Request $request): JsonResponse
    {
        $term     = $request->string('q');
        $patients = Patient::search($term)->limit(10)->get(['id', 'patient_no', 'name', 'phone', 'gender']);
        return response()->json(compact('patients'));
    }
}