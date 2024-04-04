<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SportController extends Controller
{
    public function getTotlaPostBySportCategory(Request $request){
        $data = DB::table('assets')
                ->select('sport', DB::raw('count(*) as total'))
                ->groupBy('sport')
                ->when($request->authorId, function($q)use($request){
                    $q->where('created_by', '=', $request->authorId);
                })
                ->when($request->from, function($q)use($request){
                    $q->where('published_date', '>=' ,$request->from);
                })
                ->when($request->to, function($q)use($request){
                    $q->where('published_date', '<=' ,$request->to);
                })
                ->orderBy('total','desc')
                ->pluck('total', 'sport')
                ->all();

            $labels = array_keys($data);
            $value = array_values($data);

            if(!$value){
                return response()->json([
                    'label' => $labels,
                    'value' => $value,
                    ]);
            }

            $maxValuePercentage = max($value)/100;
            $sum = 0;
            foreach ($value as $key => $total) {
                if($total < $maxValuePercentage){
                    $sum += $total;
                    unset($value[$key]);
                    unset($labels[$key]);
                }
            }
            array_push($labels,'Other');
            array_push($value, $sum);

            $labels = array_values($labels);
            $value = array_values($value);
            
            return response()->json([
                'label' => $labels,
                'value' => $value,
                ]);
    }
    public function getAuthor(){
        $author = DB::table('asset_user')
                ->select('id', 'name')
                ->get();
        return $author;
    }
    // $dataSet = Asset::selectRaw('YEAR(published_date) as YEAR, count(*) as total, sport')
    //         ->whereNotNull('published_date')
    //         ->whereNotNull('sport')
    //         ->groupBy('YEAR', 'sport')
    //         ->get();

    public function chartMonthView(){
        $years = Asset::selectRaw('YEAR(published_date) as year')
            ->whereNotNull('published_date')
            ->groupBy('year')
            ->pluck('year');

        $sports = Asset::select('sport')
            ->whereNotNull('sport')
            ->where('sport', '<>', '')
            ->groupBy('sport')
            ->pluck('sport');

        return view('monthvise',compact('years','sports'));
    }
    public function chartYearVise(){

        $years = [];
        $data = [];

        $allSport  = Asset::select('sport')
                ->whereNotNull('sport')
                ->groupBy('sport')
                ->orderBy('sport')
                ->pluck('sport');

        $allData = Asset::whereNotNull('published_date')->get();

        $dataSet = $allData->groupBy(function($post){
            return $post->published_date->format('Y');
        });
        
        foreach ($dataSet as $key => $value) {
            $data[$key] = array(); 
            foreach ($dataSet[$key]->groupBy('sport') as $sportName => $sportValue) {
                $data[$key][$sportName] = count($sportValue);
            }
            ksort($data[$key]);
        }

        foreach ($data as $key => $value) {
            for($i = 0; $i< count($allSport); $i++) {

                if(!in_array($allSport[$i], array_keys($data[$key]) )){
                    $data[$key][$allSport[$i]] = 0;
                }
            }
            ksort($data[$key]);
            $years[$key] = array_values($data[$key]);
        }
       
        return response()->json([
            'label' => $allSport,
            'data' => $years,
        ]);

    }

    public function chartMonthVise(Request $request){

        $data = Asset::selectRaw('MONTH(published_date) as month, count(*) as total')
            ->whereNotNull('published_date')
            ->whereNotNull('sport')
            ->where('sport', '<>', '');
        
        if(isset($request->year)){
            $data = $data->where(function($query)use($request){
                $query->whereYear('published_date',$request->year);
            });
        }
        if(isset($request->sport)){
            $data = $data->where(function($query)use($request){
                $query->where('sport',$request->sport);
            });
        }
        $data = $data->groupBy('month')
            ->orderBy('month')
            ->get();


        $monthnumber = $data->pluck('month')->toArray();
        $monthName = array_map(function($monthIndex){
            return Carbon::create()->month($monthIndex)->format('F');
            }, $monthnumber);

        return response([
            'labels' => $monthName,
            'value' => $data->pluck('total')->toArray(),
        ]);
    }

    public function chartSpecificMonthVise(Request $request){
        $data = DB::table('assets')
            ->whereNotNull('published_date')
            ->whereNotNull('sport')
            ->when($request->month,function($q)use($request){
                $q->selectRaw('sport, count(*) as total, MONTHNAME(published_date) as month');
                $q->groupBy('sport', 'month');
            })
            ->when($request->year,function($q)use($request){
                $q->selectRaw('YEAR(published_date) as year');
                $q->groupBy('sport', 'month', 'year');
            })
            ->having('month', $request->month)
            ->when($request->year,function($q)use($request){
                $q->having('year', $request->year);
            })
            ->pluck('total', 'sport')->toArray();
                $labels = array_keys($data);
                $value = array_values($data);
                
            return response()->json([
                'labels' => $labels,
                'values' => $value,
            ]); 
    }

    public function monthyearview(){
        $years = Asset::selectRaw('YEAR(published_date) as year')
        ->whereNotNull('published_date')
        ->groupBy('year')
        ->orderBy('year')
        ->pluck('year');
        $monthsIndex = Asset::selectRaw('MONTH(published_date) as month')
        ->whereNotNull('published_date')
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('month')->toArray();
        $monthsName = [];
        foreach ($monthsIndex as $value) {
            $monthsName[$value] = Carbon::create()->month($value)->format('F');
        }

        return view('monthyearvise',compact(['years', 'monthsName']));
    }
    public function yearMonthData(Request $request){
        $data = Asset::select(DB::raw('DATE(published_date) as date, count(*) as total'))
            ->whereNotNull('published_date')
            ->where('sport', '<>', '')
            ->whereNotNull('sport')
            ->whereMonth('published_date', $request->month)
            ->whereYear('published_date', $request->year)
            ->groupBy('date')
            ->pluck('total', 'date');
            return response()->json([
                'status' => 200,
                'data' => $data
            ]);
    }
    public function dateChart(Request $request){
        $data = Asset::select('sport',DB::raw('count(*) as total'))
            ->whereDate('published_date', $request->date)
            ->where('sport', '<>', '')
            ->whereNotNull('sport')
            ->groupBy('sport')
            ->pluck('total', 'sport');
            return response()->json([
                'status' => 200,
                'data' => $data
            ]);
    }
}
