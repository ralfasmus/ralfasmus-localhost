<?php
/**
 * Interface Request_Interface
 */
interface Request_Interface
{
    public function getConfigValue(string $key) : string;
    public function getResponse() : string;
    public function getStatus() : string;
}
