<?php

namespace app\controllers;

use flight\Engine;
use Flight;

class BesoinController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        // Récupérer tous les besoins avec leur type
        $stmt = Flight::db()->prepare("
            SELECT b.id, b.nom, b.prix, t.nom as type_nom
            FROM s3_besoin b
            JOIN s3_type_besoin t ON b.id_type_besoin = t.id
            ORDER BY b.id DESC
        ");
        $stmt->execute();
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('besoins/index', [
            'page_title'  => 'Besoins',
            'active_menu' => 'besoins',
            'besoins'     => $besoins,
        ]);
    }

    public function create(): void
    {
        // Récupérer les types de besoins
        $stmt = Flight::db()->query("SELECT id, nom FROM s3_type_besoin ORDER BY nom");
        $types = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('besoins/form', [
            'page_title'  => 'Nouveau besoin',
            'active_menu' => 'besoins',
            'action'      => '/besoins',
            'method'      => 'POST',
            'types'       => $types,
        ]);
    }

    public function store(): void
    {
        $request = Flight::request();
        $nom = $request->data->nom;
        $id_type_besoin = $request->data->id_type_besoin;
        $prix = $request->data->prix;

        $stmt = Flight::db()->prepare("
            INSERT INTO s3_besoin (nom, id_type_besoin, prix)
            VALUES (:nom, :id_type_besoin, :prix)
        ");
        $stmt->execute([
            ':nom' => $nom,
            ':id_type_besoin' => $id_type_besoin,
            ':prix' => $prix,
        ]);

        $this->app->redirect('/besoins');
    }

    public function edit(string $id): void
    {
        // Récupérer le besoin
        $stmt = Flight::db()->prepare("SELECT * FROM s3_besoin WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $besoin = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$besoin) {
            $this->app->redirect('/besoins');
            return;
        }

        // Récupérer les types de besoins
        $stmt = Flight::db()->query("SELECT id, nom FROM s3_type_besoin ORDER BY nom");
        $types = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('besoins/form', [
            'page_title'  => 'Modifier besoin',
            'active_menu' => 'besoins',
            'action'      => '/besoins/' . $id,
            'method'      => 'PUT',
            'besoin'      => $besoin,
            'types'       => $types,
        ]);
    }

    public function update(string $id): void
    {
        $request = Flight::request();
        $nom = $request->data->nom;
        $id_type_besoin = $request->data->id_type_besoin;
        $prix = $request->data->prix;

        $stmt = Flight::db()->prepare("
            UPDATE s3_besoin
            SET nom = :nom, id_type_besoin = :id_type_besoin, prix = :prix
            WHERE id = :id
        ");
        $stmt->execute([
            ':nom' => $nom,
            ':id_type_besoin' => $id_type_besoin,
            ':prix' => $prix,
            ':id' => $id,
        ]);

        $this->app->redirect('/besoins');
    }

    public function delete(string $id): void
    {
        $stmt = Flight::db()->prepare("DELETE FROM s3_besoin WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $this->app->redirect('/besoins');
    }
}
