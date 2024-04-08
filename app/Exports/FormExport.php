<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FormExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Timestamp',
            'Name',
            'Department',
            'Email',
            'Birth Date',
            'NIK',
            'NPWP',
            'KTP Address',
            'Address',
            'Blood Type',
            'Phone Number',
            'Mother Name',
            'Spouse Name',
            'Spouse Birth Date',
            'Status'
        ];
    }

    public function collection()
    {
        $data = DB::table('form as f')
        ->join('staff_data as sd', 'sd.id_form', '=', 'f.id')
        ->join('staff as s', 's.id', '=', 'sd.id_staff')
        ->join('hr_unit as hu', 'hu.IdUnit', '=', 's.id_unit')
        ->select('sd.created_at as Timestamp', 's.name', 'hu.Namaunit', 'sd.email', 'sd.birth_date', 'sd.nik', 'sd.npwp', 'sd.ktp_address', 'sd.address', 'sd.blood_type', 'sd.phone_number', 'sd.mother_name', 'sd.spouse_name', 'sd.spouse_birth_date',
        DB::raw("CASE WHEN sd.status = 0 THEN 'Pending' WHEN sd.status = 1 THEN 'Accepted' ELSE '' END as status"))
        ->where('f.id', 6)
        ->get();

        return $data;
    
    }
}