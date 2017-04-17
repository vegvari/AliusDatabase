<?php

namespace Alius\Database\Interfaces;

interface ExceptionInterface extends \Throwable
{
    const IMMUTABLE                                  = -1;

    const CONTAINER_SERVER_ALREADY_SET               = 0;
    const CONTAINER_SERVER_NOT_SET                   = 1;

    // server
    const INVALID_SERVER                             = 1000;
    const SERVER_INVALID_NAME                        = 1001;

    // server - database
    const SERVER_DATABASE_ALREADY_SET                = 12000;
    const SERVER_DATABASE_NOT_SET                    = 12001;

    // database
    const INVALID_DATABASE                           = 2000;
    const DATABASE_INVALID_NAME                      = 2001;

    // database - table
    const DATABASE_TABLE_ALREADY_SET                 = 23000;
    const DATABASE_TABLE_NOT_SET                     = 23001;

    // table
    const INVALID_TABLE                              = 3000;
    const TABLE_INVALID_NAME                         = 3001;

    // table - column
    const TABLE_COLUMN_ALREADY_SET                   = 34000;
    const TABLE_COLUMN_NOT_SET                       = 34001;

    // table - primary key
    const TABLE_PRIMARY_KEY_ALREADY_SET              = 35000;
    const TABLE_PRIMARY_KEY_NOT_SET                  = 35001;

    // table - unique key
    const TABLE_UNIQUE_KEY_ALREADY_SET               = 35100;
    const TABLE_UNIQUE_KEY_NOT_SET                   = 35101;

    // table - index
    const TABLE_INDEX_ALREADY_SET                    = 35200;
    const TABLE_INDEX_NOT_SET                        = 35201;

    // table - foreign key
    const TABLE_FOREIGN_KEY_ALREADY_SET              = 35300;
    const TABLE_FOREIGN_KEY_NOT_SET                  = 35301;

    // column
    const COLUMN_INVALID_TYPE                        = 4000;

    // column - int
    const COLUMN_INT_INVALID_AUTO_INCREMENT_NULLABLE = 41000;
    const COLUMN_INT_INVALID_AUTO_INCREMENT_DEFAULT  = 41001;
    const COLUMN_INT_INVALID_VALUE_MIN               = 41002;
    const COLUMN_INT_INVALID_VALUE_MAX               = 41003;

    // column - float
    const COLUMN_FLOAT_INVALID_PRECISION             = 42000;
    const COLUMN_FLOAT_INVALID_SCALE                 = 42001;
    const COLUMN_FLOAT_INVALID_SCALE_MAX             = 42002;
    const COLUMN_FLOAT_INVALID_VALUE                 = 42003;
    const COLUMN_FLOAT_INVALID_VALUE_MIN             = 42004;
    const COLUMN_FLOAT_INVALID_VALUE_MAX             = 42005;

    // column - string
    const COLUMN_STRING_INVALID_LENGTH               = 43000;

    // constraint - primary key
    const PRIMARY_KEY_NO_COLUMN                      = 5000;
    const PRIMARY_KEY_DUPLICATED_COLUMN              = 5001;

    // constraint - unique key
    const UNIQUE_KEY_INVALID_NAME                    = 5100;
    const UNIQUE_KEY_NO_COLUMN                       = 5101;
    const UNIQUE_KEY_DUPLICATED_COLUMN               = 5102;

    // constraint - index
    const INDEX_INVALID_NAME                         = 5200;
    const INDEX_NO_COLUMN                            = 5201;
    const INDEX_DUPLICATED_COLUMN                    = 5202;

    // constraint - foreign keys
    const FOREIGN_KEY_INVALID_NAME                   = 5300;
    const FOREIGN_KEY_NO_COLUMN                      = 5301;
    const FOREIGN_KEY_DUPLICATED_CHILD_COLUMN        = 5302;
    const FOREIGN_KEY_DUPLICATED_PARENT_COLUMN       = 5303;
    const FOREIGN_KEY_MORE_CHILD_COLUMN              = 5304;
    const FOREIGN_KEY_MORE_PARENT_COLUMN             = 5305;
    const FOREIGN_KEY_INVALID_UPDATE_RULE            = 5306;
    const FOREIGN_KEY_INVALID_DELETE_RULE            = 5307;
}
