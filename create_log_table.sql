create table log
(
    id            int auto_increment primary key,
    import_type   varchar(255) not null,
    entity_id     int          not null,
    msg           text         not null,
    date_added    datetime     not null,
    date_modified datetime     not null
)
    charset = utf8;
