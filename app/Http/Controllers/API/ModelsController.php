<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models;
use App\Http\Requests\ModelsRequest;
use App\Http\Resources\ModelsResource;
use App\Http\Resources\VersionResource;
use Illuminate\Http\Request;

class ModelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ModelsRequest $request)
    {
        //
        // dd("aa");
        $models = new Models($request->all());
        $models->train = $request->train->getClientOriginalName();
        $models->train_model = $request->train_model->getClientOriginalName();
        $models->label = $request->label->getClientOriginalName();
        Storage::disk('local')->putFileAs('models', $request->train, $request->train->getClientOriginalName());
        Storage::disk('local')->putFileAs('models', $request->train_model, $request->train_model->getClientOriginalName());
        Storage::disk('local')->putFileAs('models', $request->label, $request->label->getClientOriginalName());
        $models->save();
        $version = [
            "version" => $models->id
        ];
        Storage::disk('local')->put('version.json', json_encode($version));
        
        return new ModelsResource($models);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models  $models
     * @return \Illuminate\Http\Response
     */
    public function show(Models $models)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models  $models
     * @return \Illuminate\Http\Response
     */
    public function edit(Models $models)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models  $models
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Models $models)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models  $models
     * @return \Illuminate\Http\Response
     */
    public function destroy(Models $models)
    {
        //
    }

    public function checkModelVersion($version) 
    {
        $latestVersion = Models::latest('id')->first();
        $need_update = ($latestVersion->id > $version) ? true : false;
        // dd($need_update)
        return response()->json(['data' => ['need_update' => $need_update]]);
    }

    public function downloadTrain() {
        return Storage::download('/models/svm_train');
    }
    
    public function downloadTrainModel() {
        return Storage::download('/models/svm_train_model');
    }

    public function downloadLabel() {
        return Storage::download('/models/labelMap_train');
    }

    public function downloadVersion() {
        return Storage::download('version.json');
    }


}
