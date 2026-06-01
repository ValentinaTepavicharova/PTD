create database PTD_DB;
use PTD_DB;
create table Users (
    Id int auto_increment primary key,
    Username varchar(50) not null unique,
    Password varchar(255) not null,
    Current_level int default 0,
    Stars int default 0,
    Last_spin_date date default null
);
select * from Users;