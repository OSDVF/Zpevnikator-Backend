<?php
/**
 * @file
 * @brief Definitions of classes for data exchange
 */
/**
 * @brief Class for exchanging information about a user
 */
class UserInfo
{
    /**
     * @var string $credentials
     */
    public $credentials;
    /**
     * Wordpress-side user username
     * @var string $name
     */
    public $name;
    /**
     * Wordpress-side full user name E.g. John Doe
     * @var string $fullName
     */
    public $fullName;
    /**
     * Wordpress-side user ID in the database
     * @var int $id
     */
    public $id;
    /**
     * URL to user's avatar image
     * @var string $avatar
     */
    public $avatar;
}
/**
 * @brief Class for exchanging the user-created data for one particular user
 */
class TogetherAPIResponse
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
     * @var UserInfo $userInfo
     */
    public $userInfo;
    /**
     * Status of the request. OK if success, FAIL if failed
     * @var string $status
     */
    public $status;
}
