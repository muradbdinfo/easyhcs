<?php

namespace App\Services;

use App\Models\Tenant\Patient;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Patient::query();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (!empty($filters['blood_group'])) {
            $query->where('blood_group', $filters['blood_group']);
        }
        if (!empty($filters['patient_type'])) {
            $query->where('patient_type', $filters['patient_type']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Patient
    {
        $data['patient_no'] = $this->generatePatientNo();
        return DB::transaction(fn () => Patient::create($data));
    }

    public function update(Patient $patient, array $data): Patient
    {
        DB::transaction(fn () => $patient->update($data));
        return $patient->fresh();
    }

    public function delete(Patient $patient): void
    {
        $patient->delete();
    }

    private function generatePatientNo(): string
    {
        $year   = Carbon::now()->year;
        $prefix = "PT-{$year}-";
        $last   = Patient::withTrashed()
                         ->where('patient_no', 'like', "{$prefix}%")
                         ->orderByDesc('patient_no')
                         ->value('patient_no');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}