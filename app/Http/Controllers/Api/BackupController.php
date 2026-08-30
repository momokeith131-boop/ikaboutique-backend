<?php

namespace App\Http\Controllers\Api;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BackupController
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        return response()->json(Backup::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'sometimes|in:full,database,files',
        ]);

        $backup = Backup::create([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'full',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        $backupPath = storage_path('app/backups/' . $backup->name . '.sqlite');
        if (!is_dir(dirname($backupPath))) mkdir(dirname($backupPath), 0755, true);

        try {
            copy(database_path('database.sqlite'), $backupPath);
            $backup->file_path = $backupPath;
            $backup->file_size = filesize($backupPath);
            $backup->status = 'completed';
            $backup->completed_at = now();
            $backup->save();

            return response()->json(['message' => 'Backup créé avec succès', 'backup' => $backup], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $backup->status = 'failed';
            $backup->metadata = ['error' => $e->getMessage()];
            $backup->save();

            return response()->json(['message' => 'Erreur lors du backup', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $backup = Backup::find($id);
        if (!$backup) return response()->json(['message' => 'Backup non trouvé'], Response::HTTP_NOT_FOUND);
        return response()->json($backup);
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $backup = Backup::find($id);
        if (!$backup) return response()->json(['message' => 'Backup non trouvé'], Response::HTTP_NOT_FOUND);
        if ($backup->file_path && file_exists($backup->file_path)) unlink($backup->file_path);
        $backup->delete();
        return response()->json(['message' => 'Backup supprimé avec succès']);
    }

    public function download($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $backup = Backup::find($id);
        if (!$backup) return response()->json(['message' => 'Backup non trouvé'], Response::HTTP_NOT_FOUND);
        if (!$backup->file_path || !file_exists($backup->file_path)) {
            return response()->json(['message' => 'Fichier non trouvé'], Response::HTTP_NOT_FOUND);
        }
        return response()->download($backup->file_path, $backup->name . '.sqlite');
    }
}
