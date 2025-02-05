<?php

namespace Modules\Masterdata\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MasterData;

class MasterdataClassController extends Controller
{
    public function listMasterClass()
    {
        $data['title'] = 'Bus';
        $data['list'] = MasterData::getMasterClassList();

        return view('masterdata::class.index', $data);
    }

    public function addMasterClass()
    {
        $data['title'] = 'Tambah Master Bus';
        $data['facilities'] = MasterData::getMasterFacilities();

        return view('masterdata::class.add', $data);
    }

    public function addMasterClassStore(Request $request)
    {
        $credentials = $request->validate([
            'class_name'      => ['required', 'string'],
            'seat_count'   => ['required'],
        ]);
        
        $saveData = [
            'uuid' => generateUuid(),
            'name' => $request->class_name,
            'seat' => $request->seat_count,
        ];
        
        $saveClassId = MasterData::SaveMasterClass($saveData);
        
        foreach ($request->facilities as $key => $value) {
            $data[$key]['class_id'] = $saveClassId;
            $data[$key]['facilities_id'] = $value;
        }

        $saveClassFacilities = MasterData::saveClassFacilities($data);

        if ($saveClassId) {
            return back()->with('success', 'Master kelas tersimpan!');
        }

        return back()->with('failed', 'Master kelas gagal tersimpan!');   
    }
}
