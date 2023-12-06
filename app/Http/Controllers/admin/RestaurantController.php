<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Type;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Prendo l'id dell utente autenticato
        $userId = Auth::id();

        /*Ricerco nella tabella Restaurant un elemento con user_id == allo userId. 
        Il metodo first() permette di ottenere il dato che ci interessa come singolo oggetto, 
        piuttosto che come una collezione con , in questo caso, un singoilo oggetto
        */
        $restaurant = Restaurant::where('user_id', $userId)->first();

        return view('admin.restaurants.index', compact('restaurant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $types = Type::all();
        return view('admin.restaurants.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRestaurantRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = Restaurant::generateSlug($validated['name']);

        if ($request->has('logo')) {
            $file_path = Storage::put('img', $request->logo);
            $validated['logo'] = $file_path;
        }








        //dd($validated);
        $restaurant = Restaurant::create($validated);
        $restaurant->types()->attach($request->types);
        $restaurant->user_id = Auth::id();
        $restaurant->save();
        return to_route('admin.restaurants.index', compact('restaurant'))->with('message', 'Restaurant created successfully! You are ready to go');
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        $types = Type::all();
        return view('admin.restaurants.show', compact('restaurant', 'types'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        $types = Type::all();
        return view('admin.restaurants.edit', compact('restaurant', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant)
    {
        $validated = $request->validated();
        $validated['slug'] = $restaurant->generateSlug($request->name);
        if ($request->has('logo')) {
            $updatedLogo = $request->thumb;
            $file_path = Storage::put('logos', $updatedLogo);

            if (!is_null($restaurant->logo) && Storage::fileExists($restaurant->logo)) {
                Storage::delete($restaurant->logo);
            }

            $validated['logo'] = $file_path;
        }

        if ($request->has('types')) {
            $restaurant->types()->sync($request->types);
        }

        $restaurant->update($validated);
        return to_route('restaurants.index')->with('message', 'Restaurant updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        if (!is_null($restaurant->logo) && Storage::fileExists($restaurant->logo)) {
            Storage::delete($restaurant->logo);
        };
        $restaurant->delete();
        return to_route('admin.restaurants.index')->with('message', 'Your restaurant was deleted successfully');
    }
}
