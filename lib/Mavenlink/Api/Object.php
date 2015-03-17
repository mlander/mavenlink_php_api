<?php namespace Mavenlink\Api;

use Mavenlink\Api;

class Object
{
    public static function path()
    {
        return static::$path;
    }

    public static function getResourcesPath()
    {
        return Api::getBaseUri() . self::path() . ".json";
    }

    public static function getResourcePath($resourceId)
    {
        return Api::getBaseUri() . self::path() . "/$resourceId" . ".json";
    }

    public static function getWorkspaceResourcePath($workspaceId, $resourceId)
    {
        return Api::getBaseUri() . "workspaces/$workspaceId/" . self::path() . "/$resourceId.json";
    }

    public static function getWorkspaceResourcesPath($workspaceId)
    {
        return Api::getBaseUri() . "workspaces/$workspaceId/" . self::path() . ".json";
    }
}