<?php

namespace app\controllers;

use flight\Engine;
use Flight;

class DonController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        // Récupérer tous les dons avec leurs détails
        $stmt = Flight::db()->query("
            SELECT d.id, d.date_don,
                   GROUP_CONCAT(CONCAT(b.nom, ': ', dd.quantite, ' kg') SEPARATOR ', ') as details
            FROM s3_don d
            LEFT JOIN s3_don_details dd ON d.id = dd.id_don
            LEFT JOIN s3_besoin b ON dd.id_besoin = b.id
            GROUP BY d.id
            ORDER BY d.date_don DESC
        ");
        $dons = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('dons/index', [
            'page_title'  => 'Dons',
            'active_menu' => 'dons',
            'dons'        => $dons,
        ]);
    }

    public function create(): void
    {
        // Récupérer les besoins disponibles
        $stmt = Flight::db()->query("SELECT id, nom FROM s3_besoin ORDER BY nom");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('dons/form', [
            'page_title'  => 'Nouveau don',
            'active_menu' => 'dons',
            'action'      => '/dons',
            'method'      => 'POST',
            'besoins'     => $besoins,
        ]);
    }

    public function store(): void
    {
        $request = Flight::request();
        $date_don = $request->data->date_don;
        $besoins = $request->data->besoins ?? [];
        $quantites = $request->data->quantites ?? [];

        try {
            Flight::db()->beginTransaction();

            // Insérer le don
            $stmt = Flight::db()->prepare("
                INSERT INTO s3_don (date_don)
                VALUES (:date_don)
            ");
            $stmt->execute([':date_don' => $date_don]);
            $id_don = Flight::db()->lastInsertId();

            // Insérer les détails du don
            $stmt = Flight::db()->prepare("
                INSERT INTO s3_don_details (id_don, id_besoin, quantite)
                VALUES (:id_don, :id_besoin, :quantite)
            ");

            foreach ($besoins as $index => $id_besoin) {
                if (!empty($id_besoin) && !empty($quantites[$index])) {
                    $stmt->execute([
                        ':id_don' => $id_don,
                        ':id_besoin' => $id_besoin,
                        ':quantite' => $quantites[$index],
                    ]);
                }
            }

            Flight::db()->commit();
        } catch (\Exception $e) {
            Flight::db()->rollBack();
            throw $e;
        }

        $this->app->redirect('/dons');
    }

    public function edit(string $id): void
    {
        // Récupérer le don
        $stmt = Flight::db()->prepare("SELECT * FROM s3_don WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $don = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$don) {
            $this->app->redirect('/dons');
            return;
        }

        // Récupérer les détails du don
        $stmt = Flight::db()->prepare("
            SELECT dd.*, b.nom as besoin_nom
            FROM s3_don_details dd
            JOIN s3_besoin b ON dd.id_besoin = b.id
            WHERE dd.id_don = :id_don
        ");
        $stmt->execute([':id_don' => $id]);
        $details = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Récupérer tous les besoins
        $stmt = Flight::db()->query("SELECT id, nom FROM s3_besoin ORDER BY nom");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('dons/form', [
            'page_title'  => 'Modifier don',
            'active_menu' => 'dons',
            'action'      => '/dons/' . $id,
            'method'      => 'PUT',
            'don'         => $don,
            'details'     => $details,
            'besoins'     => $besoins,
        ]);
    }

    public function update(string $id): void
    {
        $request = Flight::request();
        $date_don = $request->data->date_don;
        $besoins = $request->data->besoins ?? [];
        $quantites = $request->data->quantites ?? [];

        try {
            Flight::db()->beginTransaction();

            // Mettre à jour le don
            $stmt = Flight::db()->prepare("
                UPDATE s3_don
                SET date_don = :date_don
                WHERE id = :id
            ");
            $stmt->execute([
                ':date_don' => $date_don,
                ':id' => $id,
            ]);

            // Supprimer les anciens détails
            $stmt = Flight::db()->prepare("DELETE FROM s3_don_details WHERE id_don = :id_don");
            $stmt->execute([':id_don' => $id]);

            // Insérer les nouveaux détails
            $stmt = Flight::db()->prepare("
                INSERT INTO s3_don_details (id_don, id_besoin, quantite)
                VALUES (:id_don, :id_besoin, :quantite)
            ");

            foreach ($besoins as $index => $id_besoin) {
                if (!empty($id_besoin) && !empty($quantites[$index])) {
                    $stmt->execute([
                        ':id_don' => $id,
                        ':id_besoin' => $id_besoin,
                        ':quantite' => $quantites[$index],
                    ]);
                }
            }

            Flight::db()->commit();
        } catch (\Exception $e) {
            Flight::db()->rollBack();
            throw $e;
        }

        $this->app->redirect('/dons');
    }

    public function delete(string $id): void
    {
        try {
            Flight::db()->beginTransaction();

            // Supprimer les détails du don
            $stmt = Flight::db()->prepare("DELETE FROM s3_don_details WHERE id_don = :id_don");
            $stmt->execute([':id_don' => $id]);

            // Supprimer le don
            $stmt = Flight::db()->prepare("DELETE FROM s3_don WHERE id = :id");
            $stmt->execute([':id' => $id]);

            Flight::db()->commit();
        } catch (\Exception $e) {
            Flight::db()->rollBack();
            throw $e;
        }

        $this->app->redirect('/dons');
    }
}
