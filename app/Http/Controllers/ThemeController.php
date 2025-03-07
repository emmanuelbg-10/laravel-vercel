<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Theme;

class ThemeController extends Controller
{
    /**
     * Muestra la lista de temas.
     */
    public function index()
    {
        $themes = Theme::all();
        return view('themes.index', compact('themes'));
    }

    /**
     * Muestra el formulario para crear un nuevo tema.
     */
    public function create()
    {
        return view('themes.create');
    }

    /**
     * Almacena un nuevo tema en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
        ]);

        Theme::create([
            'name' => $request->name,
        ]);

        return redirect()->route('themes.index')->with('success', 'Tema creado correctamente.');
    }

    /**
     * Muestra las publicaciones de un tema específico.
     */
    public function show(Theme $theme)
    {
        $posts = $theme->posts()->orderByDesc('published_at')->get();
        return view('posts.by_theme', compact('theme', 'posts'));
    }
}