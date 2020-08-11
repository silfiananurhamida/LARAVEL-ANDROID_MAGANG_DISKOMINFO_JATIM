<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Booking;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingsController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::all();
        foreach($bookings as $booking){
            $booking->r_nama = DB::table('rooms')->select('nama')->where('rooms.id', '=', $booking->r_id)->first()->nama;
        }
        
        // return response
        $response = [
            'error' => false,
            'bookings' => $bookings,
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $u_id = DB::table('users')->select('id')->where('users.username', '=', $request->get('username'))->first()->id; 

        $validator = Validator::make($request->all(), [
            'r_id'=>'required',
            'tanggal_mulai'=>'required',
            'tanggal_selesai'=>'required',
            'keperluan' => 'required',
            'file' => 'required|mimes:jpg,png,jpeg,JPG',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'msg' => 'Unvalid']);
        }

        $cek_avaiable_room = DB::table('bookings')->select('r_id')->where('bookings.r_id', '=', $request->r_id)->first();
        if(!is_null($cek_avaiable_room)){
            return response()->json(['error' => true, 'msg' => 'Not Available']);
        }

        $tanggal_mulai = $request->get('tanggal_mulai');
        $hari_mulai = substr($tanggal_mulai, 0, 2);
        $bulan_mulai = substr($tanggal_mulai, 3, 2);
        $tahun_mulai = substr($tanggal_mulai, 6, 4);
        $jam_mulai = substr($tanggal_mulai, 11, 2);
        $menit_mulai = substr($tanggal_mulai, 14, 2);
        $ampm_mulai = substr($tanggal_mulai, 17, 2);

        $tanggal_selesai = $request->get('tanggal_selesai');
        $hari_selesai = substr($tanggal_selesai, 0, 2);
        $bulan_selesai = substr($tanggal_selesai, 3, 2);
        $tahun_selesai = substr($tanggal_selesai, 6, 4);
        $jam_selesai = substr($tanggal_selesai, 11, 2);
        $menit_selesai = substr($tanggal_selesai, 14, 2);
        $ampm_selesai = substr($tanggal_selesai, 17, 2);

        if($ampm_mulai == 'pm'){
            $jam_mulai = intval($jam_mulai) + 12;
        }

        if($ampm_selesai == 'pm'){
            $jam_selesai = intval($jam_selesai) + 12;
        }

        $tanggal_mula = $tahun_mulai.'-'.$bulan_mulai.'-'.$hari_mulai.' '.$jam_mulai.':'.$menit_mulai.':'.'00';
        $tanggal_selesa = $tahun_selesai.'-'.$bulan_selesai.'-'.$hari_selesai.' '.$jam_selesai.':'.$menit_selesai.':'.'00';
        
        $booking = new \App\Booking;
        
        if (!$request->file('file')) {
            # code...
            return response()->json(['error' => true, 'msg' => 'File tidak ada']);
        }else{
            $booking->file = $request->file('file')->store('bookings','public');                
        }

        $booking->r_id=$request->get('r_id');
        $booking->u_id=$u_id;
        $booking->tanggal_mulai=$tanggal_mula;
        $booking->tanggal_selesai=$tanggal_selesa;
        $booking->keperluan=$request->get('keperluan');
        $booking->save();
        return response()->json(['error' => false, 'msg' => 'Berhasil melakukan peminjaman ruangan']);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($r_id)
    {
        // Method ini berfungsi bukan untuk mencari booking berdasarkan ID Tetapi 
        // berdasarkan ID Ruangan yang diklik oleh user

        $booking_u_id = DB::table('bookings')->select('u_id')->where('bookings.r_id', '=', $r_id)->first();
        $user_nip_nama = $user_department = DB::table('users')->select('nip', 'nama')->where('users.id', '=', $booking_u_id->u_id)->first();
        $booking = DB::table('bookings')->select('tanggal_selesai', 'tanggal_mulai', 'keperluan')->where('bookings.r_id', '=', $r_id)->first();;
        $booking->nip = $user_nip_nama->nip;
        $booking->nama = $user_nip_nama->nama;

        if (is_null($booking)) {
            // return response
            $response = [
                'error' => true,
                'message' => 'Booking not found.',
            ];
            return response()->json($response, 404);
        }
        
        // return response
        $response = [
            'error' => false,
            'msg' => 'Booking retrieved successfully.',
            'booking_room' => $booking  
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'r_id' => 'required',
            'u_id' => 'required',
            'keperluan' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'file' => 'required',
        ]);

        if ($validator->fails()) {
            // return response
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
            ];
            return response()->json($response, 404);
        }

        $booking->name = $input['name'];
        $booking->author = $input['author'];
        $booking->save();

        // return response
        $response = [
            'success' => true,
            'message' => 'Booking updated successfully.',
        ];
        return response()->json($response, 200);
    }

    public function deleteUserBooking(Request $request){
        Booking::where('r_id', $request->r_id)->delete();
        return response()->json(['error' => false, 'msg' => 'Berhasil Membatalkan Booking Ruangan']);
    }

    public function getUserBooking($username)
    {
        // Method ini berfungsi untuk mencari semua booking 
        // berdasarkan ID Ruangan yang dibooking user
        $user = Auth::user();
        if($user->username == $username){
            $booking_u_id = DB::table('users')->select('id')->where('users.username', '=', $username)->first()->id;
            $user_booking= DB::table('users')
            ->join('bookings', 'bookings.u_id', '=', 'users.id')
            ->join('rooms', 'bookings.r_id', '=', 'rooms.id')
            ->where('bookings.u_id', '=', $booking_u_id)
            ->select('bookings.r_id', 'rooms.nama', 'tanggal_selesai', 'tanggal_mulai', 'keperluan')
            ->get();

            if (is_null($user_booking)) {
                // return response
                $response = [
                    'error' => true,
                    'msg' => 'Booking not found.',
                ];
                return response()->json($response, 200);
            }
            
            // return response
            $response = [
                'error' => false,
                'msg' => 'Booking retrieved successfully.',
                'user_booking' => $user_booking 
            ];
            return response()->json($response, 200);
        }

    }
}
