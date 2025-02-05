<?php

namespace Modules\Masterdata\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MasterData;

class MasterdataFacilitiesController extends Controller
{
    public function listMasterFacilities()
    {
        $data['title'] = 'Facilities';
        $data['list'] = MasterData::getMasterFacilities();

        return view('masterdata::facilities.index', $data);
    }

    public function addMasterFacilities()
    {
        $data['title'] = 'Tambah Master Facilities';

        return view('masterdata::facilities.add', $data);
    }

    public function addMasterFacilitiesStore(Request $request)
    {
        $credentials = $request->validate([
            'facility_name'      => ['required', 'string'],
        ]);
        
        $data = [
            'uuid' => generateUuid(),
            'name' => $request->facility_name,
        ];

        $save = MasterData::saveFacility($data);

        if ($save) {
            return back()->with('success', 'Master fasilitas tersimpan!');
        }

        return back()->with('failed', 'Master fasilitas gagal tersimpan!');   
    }
}
