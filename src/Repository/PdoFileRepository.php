<?php

namespace LinkORB\PdfGenerationServer\Repository;

use LinkORB\PdfGenerationServer\Model\File;
use PDO;

class PdoFileRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getById($id)
    {
        $statement = $this->pdo->prepare(
            "SELECT *
            FROM file
            WHERE id=:id
            LIMIT 1"
        );
        $statement->execute(array(
            'id' => $id,
        ));
        $row = $statement->fetch();
        return $row ? $this->rowToObject($row) : null;
    }

    public function getAll()
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM file ORDER BY created_at DESC"
        );
        $statement->execute();
        $rows = $statement->fetchAll();
        $objects = array();
        foreach ($rows as $row) {
            $objects[] = $this->rowToObject($row);
        }

        return $objects;
    }

    public function add(File $file)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO file (id, path, created_at) VALUES (:id, :path, :created_at)'
        );

        $statement->execute([
            'id' => $file->getId(),
            'path' => $file->getPath(),
            'created_at' => $file->getCreatedAt(),
        ]);

        return true;
    }

    public function delete(File $file)
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM file WHERE id=:id"
        );
        $statement->execute(
            [
                'id' => $file->getId(),
            ]
        );
    }

    private function rowToObject($row)
    {
        if (!$row) {
            return null;
        }
        $obj = new File();
        $obj->setId($row['id'])
            ->setPath($row['path'])
            ->setCreatedAt($row['created_at']);

        return $obj;
    }
}