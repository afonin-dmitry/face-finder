<?php
require_once 'FaceFinderInterface.php';

/**
 * Class for searching, adding and deleting all faces
 */
class FaceFinder implements FaceFinderInterface
{
    private $dbh;
    
    /**
     * Prepared select statement
     *
     * @var PDOStatement
     */
    private $select;
    
    public function __construct()
    {
        $this->connect()
            ->build()
            ->prepareSelect();
    }
    
    private function connect()
    {
        // dsn w/o dbname
        $dsn  = 'mysql:host=localhost:3306;';
        $user = 'root';
        $pass = '';
        
        $this->dbh = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        return $this;
    }
    
    /**
     * Creates database structure
     *
     * @return $this
     */
    private function build()
    {
        $queries = [
            "CREATE DATABASE IF NOT EXISTS face_finder;",
            "USE face_finder;",
            "CREATE TABLE IF NOT EXISTS faces (
                id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    race tinyint NOT NULL DEFAULT 0,
                    emotion tinyint NOT NULL DEFAULT 0,
                    oldness tinyint NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)
                ) ENGINE=MyISAM;"
        ];
        
        $this->dbh->exec(implode($queries));
        
        return $this;
    }
    
    /**
     * Prepares query to select five similar faces
     */
    private function prepareSelect()
    {
        $this->select = $this->dbh->prepare(
            "SELECT id, emotion, race, oldness,
                SQRT( POW( (emotion - :emotion), 2 ) + POW( (race - :race), 2 ) + POW( (oldness - :oldness), 2) ) AS l
            FROM (SELECT race, emotion, oldness, id FROM faces LIMIT 10000) as faces
            ORDER BY l
            LIMIT 5"
        );
    }
    
    /**
     * {@inheritDoc}
     */
    public function resolve(FaceInterface $face): array
    {
        if ($face->getId() === 0)
            $this->save($face);
        
        return $this->getSimilar($face);
    }
    
    /**
     * Returns 5 similar faces
     */
    private function getSimilar(FaceInterface $face): array
    {
        $this->select->execute([
            ':emotion' => $face->getEmotion(),
            ':oldness' => $face->getOldness(),
            ':race'    => $face->getRace()
        ]);
        
        return $this->select->fetchAll(PDO::FETCH_FUNC, function($id, $emotion, $race, $oldness) {
            return new Face($race, $emotion, $oldness, $id);
        });
    }
    
    /**
     * Saves face to database
     */
    private function save(FaceInterface $face): void
    {
        static $sth;
        if (!$sth) {
            $sth = $this->dbh->prepare(
                "INSERT INTO faces
                    (emotion, race, oldness) VALUES
                    (:emotion, :race, :oldness)"
            );
        }
        
        $sth->execute([
            ':emotion' => $face->getEmotion(),
            ':oldness' => $face->getOldness(),
            ':race'    => $face->getRace()
        ]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function flush(): void
    {
        $this->dbh->exec("TRUNCATE faces");
    }
}