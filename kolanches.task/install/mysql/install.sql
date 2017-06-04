create table kolanches_task
(
  ID int not null auto_increment
,  NAME varchar(255) not null
,  DESCRIPTION text null
,  COMPLETE char(1) not null default 'N'

,  primary key (ID)
,  index IX_KOLANCHES_TASK_1(COMPLETE)
);
