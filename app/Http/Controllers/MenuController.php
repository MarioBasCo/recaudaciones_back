<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->get();
        $menus->makeHidden(['created_at', 'updated_at']);

        // Ocultar los timestamps en los submenús
        foreach ($menus as $menu) {
            $menu->children->makeHidden(['created_at', 'updated_at']);
        }

        return response()->json($menus);
    }

    public function store(Request $request)
    {
        $menu = Menu::create([
            'title' => $request->input('title'),
            'parent_id' => $request->input('parent_id'),
        ]);

        return response()->json($menu, 201);
    }

    public function show($id)
    {
        $menu = Menu::with('children')->findOrFail($id);
        return response()->json($menu);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->title = $request->input('title');
        $menu->parent_id = $request->input('parent_id');
        $menu->save();

        return response()->json($menu);
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json(null, 204);
    }
}
