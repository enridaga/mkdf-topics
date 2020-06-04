create table if not exists collection
(
    id            int auto_increment
        primary key,
    title         varchar(255)                       not null,
    description   text                       not null,
    user_id       int                                not null,
    date_created  datetime default CURRENT_TIMESTAMP null,
    date_modified datetime default CURRENT_TIMESTAMP null,
    constraint collection__user_id
        foreign key (user_id) references user (id)
            on delete cascade
);

create table if not exists collection__dataset
(
    collection_id int not null,
    dataset_id    int not null,
    constraint collection_id
        unique (collection_id, dataset_id),
    constraint collection__dataset_to_collection
        foreign key (collection_id) references collection (id)
            on delete cascade,
    constraint collection__dataset_to_dataset
        foreign key (dataset_id) references dataset (id)
            on delete cascade
);

create table if not exists tag
(
    id   int auto_increment
        primary key,
    name varchar(50) null,
    constraint tag_pk
        unique (name)
);

create table if not exists dataset__tag
(
    id         int auto_increment
        primary key,
    dataset_id int not null,
    tag_id     int not null,
    constraint dataset__tag_dataset_id_fk
        foreign key (dataset_id) references dataset (id)
            on delete cascade,
    constraint dataset__tag_tag_id_fk
        foreign key (tag_id) references tag (id)
            on delete cascade
);