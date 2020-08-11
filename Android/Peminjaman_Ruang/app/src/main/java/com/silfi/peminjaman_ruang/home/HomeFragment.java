package com.silfi.peminjaman_ruang.home;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.silfi.peminjaman_ruang.AdapterListRooms;
import com.silfi.peminjaman_ruang.R;
import com.silfi.peminjaman_ruang.Room;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import static com.silfi.peminjaman_ruang.Constant.API_LIST_ROOMS;

public class HomeFragment extends Fragment {

    RecyclerView mRecyclerView;
    RecyclerView.Adapter mAdapter;
    RecyclerView.LayoutManager mManager;
    RequestQueue mRequest;
    List<Room> lRooms = new ArrayList<>();
    ProgressBar mProgressBar;

    public View onCreateView(@NonNull LayoutInflater inflater,
                             ViewGroup container, Bundle savedInstanceState) {
        View root = inflater.inflate(R.layout.fragment_home, container, false);

        mRecyclerView = (RecyclerView) root.findViewById(R.id.rcV_rooms);
        mManager = new LinearLayoutManager(getActivity(), LinearLayoutManager.VERTICAL,false);
        mProgressBar = root.findViewById(R.id.progressBar_home);
        mProgressBar.setVisibility(View.VISIBLE);
        request();
        mRecyclerView.setLayoutManager(mManager);
        mAdapter =  new AdapterListRooms(lRooms, getActivity());
        mRecyclerView.setAdapter(mAdapter);
        return root;
    }

    private void request(){
        lRooms.clear();
        JsonObjectRequest requestRooms = new JsonObjectRequest(Request.Method.GET, API_LIST_ROOMS, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject object) {
                        try{
                            if(!object.getBoolean("error")){
                                mProgressBar.setVisibility(View.INVISIBLE);
                                JSONArray rooms = object.getJSONArray("rooms");
                                for (int i = 0; i < rooms.length(); i++) {
                                    JSONObject obj = rooms.getJSONObject(i);
                                    lRooms.add(new Room(
                                            obj.getInt("id"),
                                            obj.getString("nama"),
                                            obj.getString("foto")
                                    ));
                                }
                            }
                        }catch (JSONException e){
                            e.printStackTrace();
                        }
                        mAdapter.notifyDataSetChanged();
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                mProgressBar.setVisibility(View.INVISIBLE);
                Log.d("ERRORRequest", "Error : " + error.getMessage());
            }
        }){
            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
                Map<String, String> params = new HashMap<String, String>();
                params.put("Content-Type", "application/json; charset=UTF-8");
                params.put("Authorization", "Bearer "+ prefs.getString("access_token", null));
                return params;
            }
        };
        mRequest = Volley.newRequestQueue(getActivity());
        mRequest.add(requestRooms);
    }
}