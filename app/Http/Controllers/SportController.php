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
                })->when($request->to, function($q)use($request){
                    $q->where('published_date', '<=' ,$request->to);
                })
                ->orderBy('total','desc')
                // ->having('total', '>' , 0)
                ->pluck('total', 'sport')
                ->all();
            $labels = array_keys($data);
            $value = array_values($data);
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

    public function chartYearVise(){
        $allSport  = Asset::select('sport')->whereNotNull('sport')->groupBy('sport')->orderBy('sport')->pluck('sport');
        $allData = Asset::whereNotNull('published_date')->get();

        $dataSet = $allData->groupBy(function($post){
            return $post->published_date->format('Y');
        });
        $years = [];
        $data = [];
        foreach ($dataSet as $key => $value) {
            $data[$key] = array(); 
            foreach ($dataSet[$key]->groupBy('sport') as $sportName => $sportValue) {
                $data[$key][$sportName] = count($sportValue);
            }
            ksort($data[$key]);
        }
        // return [$data,$allSport];
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

}
