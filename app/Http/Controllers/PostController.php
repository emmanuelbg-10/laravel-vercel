<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Muestra una lista de recursos.
     */
    public function index()
    {
        $user = auth()->user(); // Obtiene el usuario autenticado
        $posts = $user->posts; // Obtiene las publicaciones del usuario
        return view('posts.index', compact('posts')); // Retorna la vista con las publicaciones
    }

    /**
     * Muestra el formulario para crear un nuevo recurso.
     */
    public function create()
    {
        $themes = Theme::all();
        return view('posts.create', compact('themes'));
    }
    
    public function store(Request $request)
    {
        // Valida los datos del formulario
        $validated = $request->validate([
            'title' => 'required|unique:posts|min:3|max:255',
            'summary' => 'max:2000',
            'body' => 'required',
            'published_at' => 'required|date',
            'theme_id' => 'required|exists:themes,id',
        ]);
    
        // Crea una nueva publicación
        $post = new Post();
        $post->user_id = Auth::id(); // Asigna el ID del usuario autenticado
        $post->title = $validated['title'];
        $post->summary = $validated['summary'];
        $post->body = $validated['body'];
        $post->published_at = $validated['published_at'];
        $post->theme_id = $validated['theme_id'];
        $post->save(); // Guarda la publicación en la base de datos
    
        // Redirige a la lista de publicaciones con un mensaje de éxito
        return redirect()->route('posts.index')
            ->with('success', 'Publicación creada correctamente');
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post')); // Retorna la vista para mostrar una publicación específica
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(Post $post)
    {
        // Verifica si el usuario autenticado es el autor de la publicación
        if ($post->user_id != Auth::id()) {
            return redirect()->route('posts.index')
                ->with('error', 'No puedes editar una publicación de la que no eres el autor.');
        }

        $themes = Theme::all();

        return view('posts.edit', compact('post', 'themes')); // Retorna la vista para editar la publicación
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, Post $post)
    {
        // Verifica si el usuario autenticado es el autor de la publicación
        if ($post->user_id != Auth::id()) {
            return redirect()->route('posts.index')
                ->with('error', 'No puedes editar una publicación de la que no eres el autor.');
        }

        // Valida los datos del formulario
        $request->validate([
            'title' => 'required|min:3|max:255',
            'summary' => 'max:2000',
            'body' => 'required',
            'published_at' => 'required|date',
            'theme_id' => 'required|exists:themes,id',
        ]);

        // Actualiza la publicación
        $post->title = $request->title;
        $post->summary = $request->summary;
        $post->body = $request->body;
        $post->published_at = $request->published_at;
        $post->theme_id = $request->theme_id;
        $post->save();

        // Redirige a la vista de la publicación actualizada con un mensaje de éxito
        return redirect()->route('posts.show', $post)
            ->with('success', 'Publicación actualizada correctamente');
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(Post $post)
    {
        // Verifica si el usuario autenticado es el autor de la publicación
        if ($post->user_id == Auth::id()) {
            $post->votedUsers()->detach(); // Elimina los votos asociados a la publicación
            $post->delete(); // Elimina la publicación
            return redirect()->route('posts.index')
                ->with('success', 'Publicación eliminada correctamente.');
        } else {
            return redirect()->route('posts.index')
                ->with('error', 'No puedes eliminar una publicación de la que no eres el autor.');
        }
    }

    /**
     * Muestra la página de inicio con las publicaciones.
     */
    public function home()
    {
        // Consulta las publicaciones ordenadas por fecha de publicación
        $query = Post::select('id', 'title', 'summary', 'published_at', 'user_id', 'theme_id')
            ->where('published_at', '<=', \Carbon\Carbon::today())
            ->orderByDesc('published_at');

        // Obtiene las primeras 5 publicaciones
        $firstPosts = $query->take(5)->get();

        // Obtiene las siguientes 20 publicaciones
        $otherPosts = $query->skip(5)->take(20)->get();

        // Retorna la vista de inicio con las publicaciones
        return view('home', compact('firstPosts', 'otherPosts'));
    }

    /**
     * Muestra una publicación específica para leer.
     */
    public function read($id)
    {
        $post = Post::find($id); // Encuentra la publicación por ID
        return view('posts.read', compact('post')); // Retorna la vista para leer la publicación
    }

    /**
     * Permite votar o quitar el voto de una publicación.
     */
    public function vote(Request $request, Post $post)
    {
        $user = Auth::user(); // Obtiene el usuario autenticado
        $voteType = $request->input('vote_type');
    
        // Verifica si el valor de vote_type es válido
        if (!in_array($voteType, ['like', 'dislike'])) {
            return redirect()->route('home')->with('error', 'Voto inválido.');
        }
    
        // Verifica si el usuario ya ha votado por esta publicación
        $existingVote = $post->votedUsers()->where('user_id', $user->id)->first();
    
        if ($existingVote) {
            // Si el usuario ya ha votado y el nuevo voto es diferente, actualiza el voto
            if ($existingVote->pivot->vote_type !== $voteType) {
                $post->votedUsers()->updateExistingPivot($user->id, ['vote_type' => $voteType]);
            } else {
                // Si el usuario ya ha votado y el nuevo voto es el mismo, elimina el voto
                $post->votedUsers()->detach($user->id);
            }
        } else {
            // Si el usuario no ha votado, crea un nuevo voto
            $post->votedUsers()->attach($user->id, ['vote_type' => $voteType]);
        }
    
        return redirect()->route('home'); // Redirige a la página de inicio
    }

    public function postsByTheme($themeId)
{
    $theme = Theme::findOrFail($themeId); // Encuentra el tema por ID o lanza un error 404
    $posts = $theme->posts()->orderByDesc('published_at')->get(); // Obtiene las publicaciones del tema ordenadas por fecha de publicación

    return view('posts.by_theme', compact('theme', 'posts')); // Retorna la vista con el tema y sus publicaciones
}

}
