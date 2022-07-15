<?php

namespace Wgirhad\Migrate;

enum Driver: string
{
    case Sqlite = 'sqlite';
    case PostgreSql = 'pgsql';
    case MySql = 'mysql';
}
