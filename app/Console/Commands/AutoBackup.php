<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoBackup extends Command
{
    protected $signature = 'backup:auto';
    protected $description = 'Effectue une sauvegarde automatique de la base de données';

    public function handle()
    {
        $backupName = 'backup_' . now()->format('Y-m-d_H-i-s');

        $this->info('🔄 Démarrage de la sauvegarde...');

        try {
            // Créer le dossier backups s'il n'existe pas
            $backupPath = storage_path('app/backups');
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Copier la base de données
            $dbPath = $backupPath . '/' . $backupName . '.sqlite';
            copy(database_path('database.sqlite'), $dbPath);

            // Enregistrer dans la table backups
            Backup::create([
                'name' => $backupName,
                'type' => 'full',
                'status' => 'completed',
                'file_path' => $dbPath,
                'file_size' => filesize($dbPath),
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            // Supprimer les sauvegardes de plus de 30 jours
            $this->cleanOldBackups();

            $this->info('✅ Sauvegarde terminée avec succès !');
            $this->info('📁 Fichier : ' . $backupName . '.sqlite');
            $this->info('📊 Taille : ' . round(filesize($dbPath) / 1024, 2) . ' KB');

            Log::info('Sauvegarde automatique effectuée', ['file' => $backupName]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la sauvegarde : ' . $e->getMessage());
            Log::error('Erreur sauvegarde automatique', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    private function cleanOldBackups()
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath . '/backup_*.sqlite');

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $daysOld = (time() - $fileTime) / 86400;

            if ($daysOld > 30) {
                unlink($file);
                $this->line('🗑️ Fichier supprimé : ' . basename($file));
            }
        }
    }
}
