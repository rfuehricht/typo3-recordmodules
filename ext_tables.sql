CREATE TABLE tx_recordmodules_config (
    pid int(11) DEFAULT 0 NOT NULL,

    tablename varchar(255) DEFAULT '' NOT NULL,
    pids varchar(255) DEFAULT '' NOT NULL,
    root_level tinyint(1) unsigned DEFAULT 0 NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    iconIdentifier varchar(255) DEFAULT '' NOT NULL,
    icon int(11) unsigned DEFAULT 0 NOT NULL,
    parent varchar(255) DEFAULT '' NOT NULL,

    INDEX parent (pid)
);