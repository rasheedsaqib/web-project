<?php

namespace App\Http\Controllers\front;

use App\About;
use App\Banner;
use App\Category;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Item;
use App\Pincode;
use App\Ratting;
use App\Slider;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class HomeController extends Controller
{
    public function index()
    {
        $getslider = Slider::all();
        $getcategory = Category::where('is_available', '=', '1')->where('is_deleted', '2')->get();
        $getabout = About::where('id', '=', '1')->first();
        $user_id = Session::get('id');
        $getitem = Item::with(['category', 'itemimage'])->select('item.cat_id', 'item.id', 'item.item_name', 'item.item_price', 'item.item_description', DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function ($query) use ($user_id) {
                $query->on('favorite.item_id', '=', 'item.id')
                    ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.item_status', '1')
            ->where('item.is_deleted', '2')
            ->orderby('cat_id')->get();
        $getreview = Ratting::with('users')->get();

        $getbanner = Banner::orderby('id', 'desc')->get();

        $getdata = User::select('currency')->where('type', '1')->first();
        return view('front.home', compact('getslider', 'getcategory', 'getabout', 'getitem', 'getreview', 'getbanner', 'getdata'));
    }

    public function contact(Request $request)
    {
        if ($request->firstname == "") {
            return response()->json(["status" => 0, "message" => "First name is required"], 200);
        }
        if ($request->lastname == "") {
            return response()->json(["status" => 0, "message" => "Last name is required"], 200);
        }
        if ($request->email == "") {
            return response()->json(["status" => 0, "message" => "Email is required"], 200);
        }
        if ($request->message == "") {
            return response()->json(["status" => 0, "message" => "Message is required"], 200);
        }
        $category = new Contact;
        $category->firstname = $request->firstname;
        $category->lastname = $request->lastname;
        $category->email = $request->email;
        $category->message = $request->message;
        $category->save();

        if ($category) {
            return response()->json(['status' => 1, 'message' => 'Your message has been successfully sent.!'], 200);
        } else {
            return response()->json(['status' => 2, 'message' => 'Something went wrong.'], 200);
        }
    }


    public function notallow()
    {
        return view('front.405');
    }
}
