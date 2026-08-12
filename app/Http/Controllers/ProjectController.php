<?php

namespace App\Http\Controllers;

/**
 * Controlador encargado de gestionar los proyectos.
 * Conecta las rutas con el modelo Project (Eloquent) y con las vistas.
 */

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Requerimiento 1: Listar todos los proyectos.
     */
    public function index()
    {
        $proyectos = Project::all();

        return view('projects.index', ['proyectos' => $proyectos]);
    }

    // Requerimiento 5: Obtener un proyecto por su id
    public function show(int $id)
    {
        $proyecto = Project::find($id);

        if (!$proyecto) {
            return redirect()->route('projects.index')->with('error', "No existe un proyecto con id {$id}.");
        }

        return view('projects.show', ['proyecto' => $proyecto]);
    }

    // Requerimiento 2 (parte 1): Muestra el formulario vacío para crear un proyecto
    public function create()
    {
        return view('projects.create');
    }

    // Requerimiento 2 (parte 2): Procesa el formulario y crea el proyecto
    public function store(Request $request)
    {
        $validado = $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha_inicio' => 'required|date',
            'estado' => 'required|string|max:50',
            'responsable' => 'required|string|max:150',
            'monto' => 'required|numeric|min:0',
        ]);

        // TODO: reemplazar por auth()->id() una vez que el login esté funcionando
        $validado['created_by'] = auth()->id() ?? 1;

        Project::create($validado);

        return redirect()->route('projects.index')->with('success', 'Proyecto creado correctamente.');
    }

    // Requerimiento 4 (parte 1): Muestra el formulario con los datos del proyecto a editar
    public function edit(int $id)
    {
        $proyecto = Project::find($id);

        if (!$proyecto) {
            return redirect()->route('projects.index')->with('error', "No existe un proyecto con id {$id}.");
        }

        return view('projects.edit', ['proyecto' => $proyecto]);
    }

    // Requerimiento 4 (parte 2): Procesa el formulario y actualiza el proyecto
    public function update(Request $request, int $id)
    {
        $proyecto = Project::find($id);

        if (!$proyecto) {
            return redirect()->route('projects.index')->with('error', "No existe un proyecto con id {$id}.");
        }

        $validado = $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha_inicio' => 'required|date',
            'estado' => 'required|string|max:50',
            'responsable' => 'required|string|max:150',
            'monto' => 'required|numeric|min:0',
        ]);

        $proyecto->update($validado);

        return redirect()->route('projects.index')->with('success', 'Proyecto actualizado correctamente.');
    }

    // Requerimiento 3 (parte 1): Muestra la confirmación antes de eliminar
    public function confirmDelete(int $id)
    {
        $proyecto = Project::find($id);

        if (!$proyecto) {
            return redirect()->route('projects.index')->with('error', "No existe un proyecto con id {$id}.");
        }

        return view('projects.delete', ['proyecto' => $proyecto]);
    }

    // Requerimiento 3 (parte 2): Elimina el proyecto por su id
    public function destroy(int $id)
    {
        $proyecto = Project::find($id);

        if (!$proyecto) {
            return redirect()->route('projects.index')->with('error', "No existe un proyecto con id {$id}.");
        }

        $proyecto->delete();

        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado correctamente.');
    }
}