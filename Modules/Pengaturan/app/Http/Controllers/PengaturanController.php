<?php

namespace Modules\Pengaturan\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Pengaturan;

class PengaturanController extends Controller
{
    public function akap()
    {
        $data['all_trip_route'] = Pengaturan::getAllTripRoute();
        $data['trip_route_group'] = Pengaturan::getTripRouteGroup();

        $data['selected_trip_route'] = array();

        foreach ($data['trip_route_group'] as $value) {
            $rx = explode(",", $value->route_x);
            foreach ($rx as $rxv) {
                array_push($data['selected_trip_route'], $rxv);
            }
            $ry = explode(",", $value->route_y);
            foreach ($ry as $ryv) {
                array_push($data['selected_trip_route'], $ryv);
            }
        }

        // dd($data['selected_trip_route']);


        $data['title'] = 'Pengaturan Akap';
        return view('pengaturan::akap', $data);
    }

    public function akapCreate(Request $request)
    {
        $request->validate([
            'name_x' => ['required'],
            'name_y' => ['required'],
        ]);

        $data = [
            'name_x' => $request->input('name_x'),
            'route_x' => implode(',',  $request->input('route_x')),
            'name_y' => $request->input('name_y'),
            'route_y' => implode(',',  $request->input('route_y')),
        ];

        $response = Pengaturan::createTripRouteGroup($data);

        if ($response) {
            return back()->with('success', 'Data berhasil dibuat');
        }

        return back()->with('failed', 'Data gagal dibuat');
    }



    public function akapUpdate(Request $request)
    {
        $data = $request->input('data');

        foreach ($data as &$value) {
            $value['route_x'] = implode(',',  $value['route_x']);
            $value['route_y'] = implode(',',  $value['route_y']);

            Pengaturan::updateTripRouteGroup($value);
        }

        return back()->with('success', 'Data berhasil diubah');
    }

    public function pariwisata()
    {
        $data['title'] = 'Pengaturan Pariwisata';
        return view('pengaturan::pariwisata', $data);
    }
}
