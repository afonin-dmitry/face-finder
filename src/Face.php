<?php
require_once 'FaceInterface.php';

/**
 * Face parameters class
 */
class Face implements FaceInterface
{
    private $id;
    private $emotion;
    private $oldness;
    private $race;
    
    public function __construct(int $race, int $emotion, int $oldness, int $id = 0)
    {
        $this->setRace($race);
        $this->setEmotion($emotion);
        $this->setOldness($oldness);
        $this->setId($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getEmotion(): int
    {
        return $this->emotion;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOldness(): int
    {
        return $this->oldness;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getRace(): int
    {
        return $this->race;
    }
    
    private function setId(int $id): void
    {
        if ($id < 0)
            throw new RangeException("Id must not be zero");
        
        $this->id = $id;
    }
    
    private function setEmotion(int $emotion): void
    {
        if ($emotion > 1000 || $emotion < 0)
            throw new RangeException("Emotion must be between 0 and 1000");
        
        $this->emotion = $emotion;
    }
    
    private function setOldness(int $oldness): void
    {
        if ($oldness > 1000 || $oldness < 0)
            throw new RangeException("Oldness must be between 0 and 1000");
        
        $this->oldness = $oldness;
    }
    
    private function setRace(int $race): void
    {
        if ($race > 100 || $race < 0)
            throw new RangeException("Race must be between 0 and 100");
        
        $this->race = $race;
    }
}