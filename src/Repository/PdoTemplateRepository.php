<?php

namespace LinkORB\PdfGenerationServer\Repository;

use LinkORB\PdfGenerationServer\Model\Template;
use PDO;

class PdoTemplateRepository
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
            FROM template
            WHERE id=:id
            LIMIT 1"
        );
        $statement->execute(array(
            'id' => $id,
        ));
        $row = $statement->fetch();
        return $row ? $this->rowToObject($row) : null;
    }

    public function getByName($name)
    {
        $statement = $this->pdo->prepare(
            "SELECT *
            FROM template
            WHERE name=:name
            LIMIT 1"
        );
        $statement->execute(array(
            'name' => $name
        ));
        $row = $statement->fetch();

        return $row ? $this->rowToObject($row) : null;
    }

    public function getAll()
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM template"
        );
        $statement->execute();
        $rows = $statement->fetchAll();
        $objects = array();
        foreach ($rows as $row) {
            $objects[] = $this->rowToObject($row);
        }

        return $objects;
    }

    public function add(Template $template)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO template () VALUES ()'
        );
        $statement->execute();
        $template->setId($this->pdo->lastInsertId());
        $this->update($template);

        return true;
    }

    public function update(Template $template)
    {
        $statement = $this->pdo->prepare(
            "UPDATE template
             SET name=:name, path=:path, description=:description, edited_at=:edited_at, created_at=:created_at
             WHERE id=:id"
        );

        $st = [
            'id' => $template->getId(),
            'name' => $template->getName(),
            'path' => $template->getPath(),
            'description' => $template->getDescription(),
            'edited_at' => date('Y-m-d H:i:s'),
        ];

        if($template->getCreatedAt() == null) {
            $st['created_at'] = date('Y-m-d H:i:s');
        }
        else {
            $st['created_at'] = $template->getCreatedAt();
        }

        $statement->execute($st);

        return $template;
    }

    public function delete(Template $template)
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM template WHERE id=:id"
        );
        $statement->execute(
            [
                'id' => $template->getId(),
            ]
        );
    }

    private function rowToObject($row)
    {
        if (!$row) {
            return null;
        }
        $obj = new Template();
        $obj->setId($row['id'])
            ->setName($row['name'])
            ->setPath($row['path'])
            ->setDescription($row['description'])
            ->setEditedAt($row['edited_at'])
            ->setCreatedAt($row['created_at']);

        return $obj;
    }
}
