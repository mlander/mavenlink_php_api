<?php namespace Mavenlink\Api;

class Object
{
    public static function path()
    {
        return static::$path;
    }

    public static function getResourcesPath()
    {
        return MavenlinkApi::getBaseUri() . self::path() . ".json";
    }

    public static function getResourcePath($resourceId)
    {
        return MavenlinkApi::getBaseUri() . self::path() . "/$resourceId" . ".json";
    }

    public static function getWorkspaceResourcePath($workspaceId, $resourceId)
    {
        return MavenlinkApi::getBaseUri() . "workspaces/$workspaceId/" . self::path() . "/$resourceId.json";
    }

    public static function getWorkspaceResourcesPath($workspaceId)
    {
        return MavenlinkApi::getBaseUri() . "workspaces/$workspaceId/" . self::path() . ".json";
    }
}