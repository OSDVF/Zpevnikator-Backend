<?php
/**
 * @brief Agregated user-created data for one particular user
 */
class User
{
    /**
     * @var Song[] $songs
     */
    public $songs;
    /**
     * @var Group[] $groups
     */
    public $groups;
    /**
     * @var Playlist[] $playlists
     */
    public $playlists;
    /**
     * @var Note[] $notes
     */
    public $notes;
    /**
     * @var string $credentials
     */
    public $credentials;
    /**
     * Wordpress-side user ID in the database
     * @var int $id
     */
    public $id;
    /**
     * Status of the request. OK if success, FAIL if failed
     * @var string $status
     */
	public $status;
}