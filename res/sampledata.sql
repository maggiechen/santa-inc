/*
~~~COMMON ERRORS~~~
If you run into an error running this on the servers and get stuck, go through this list first:

-Remember to have 'values' keyword on insert
-Strings w/ oracle will use 'single quotes', not "double quotes"
-Make sure the date is valid (watch out for february). Format is YYYY-MM-DD
-If you need an apostrophe in your string, usage is as follows: 'Tinky''s store' (yes, 2 apostrophes!)
-It's char(40), not char[40]

And if you do see a glaring error with the table, please check and (if needed) correct the diagram and report as well.
     
*/
drop table Username cascade constraints;
drop table Item cascade constraints;
drop table Toy_isFor cascade constraints;
drop table Sleigh cascade constraints;
drop table Child cascade constraints;
drop table Wants cascade constraints;
drop table ManagerElf cascade constraints;
drop table FulltimeElf_mng_mon cascade constraints;
drop table UnionWorker cascade constraints;
drop table InternElf_train cascade constraints;
drop table takeCareOf cascade constraints;
drop table Reindeer_drives cascade constraints;

create table Username(
	uname char(40),
	primary key (uname)
);


insert into Username values ('sailor.south');
insert into Username values ('smithereeny');
insert into Username values ('compassmaster' );
insert into Username values ('yeastybeasty');
insert into Username values ('idestroydbs');
insert into Username values ('theotherone');
insert into Username values ('DanceALot');
insert into Username values ('Coffee5Ever');
insert into Username values ('toomuchmusic');
insert into Username values ('turingMachine');
insert into Username values ('corgi358');
insert into Username values ('tinkerwinker');
insert into Username values('blardigus');
insert into Username values('spider.nocturne');
insert into Username values('shoesprayer');
insert into Username values('witchhazel999');
insert into Username values('lucaluca');
insert into Username values('notarthur');
insert into Username values ('brandonblimp');
insert into Username values ('yapyap');
insert into Username values ('carrotdestroyer');
insert into Username values ('serialpig');
insert into Username values('honkytonk3');
insert into Username values('quinzelqueen');
insert into Username values('obvileaguer');
insert into Username values('notleaguer');
insert into Username values('assassin.of.the.night');
insert into Username values('easterjack2');


create table Item(
name char(40),
iModel integer,
iSerial integer, 
PRIMARY KEY(iModel, iSerial));

insert into Item values ( 'Rocket launcher', 20, 39852);
insert into Item values ( 'Rocket launcher', 20, 2953);
insert into Item values ( 'Pogo stick', 76, 16593);
insert into Item values ( 'Skateboard', 58, 82752);
insert into Item values ( 'Applesoft Quantum Computer', 1, 1);
insert into Item values ( 'Teddy bear', 852, 432108);
insert into Item values ('Hoola hoop', 32, 65853);
insert into Item values ( 'Bicycle', 6874, 11586);
insert into Item values ( 'Bicycle', 6874, 2857);
insert into Item values ( 'Dollhouse', 21, 423);

create table Sleigh(
sModel integer, 
sSerial integer, 
condition integer,
sName char(40),
PRIMARY KEY(sModel, sSerial));
--	condition: 0 = perfect, 1 = good, 2 = damaged, 3 = unusable


insert into Sleigh values (9862, 458, 2, '2005 Toyotair Camree');
insert into Sleigh values (5524, 986, 0, '2013 Toyotair Camree');
insert into Sleigh values (5498, 888, 1, '2008 Acurara BMX');
insert into Sleigh values (1960, 135, 3, '1995 Hahndah Civil');
insert into Sleigh values (1960, 120, 3, '1995 Hahndah Civil');
insert into Sleigh values (2608, 552, 3, 'A.D. 400 Faint-Nicolahs Shoos');

create table Child (
cName char(40), 
lat real, 
lon real, 
rating integer, 
age integer, 
CID integer,
PRIMARY KEY(CID));
--	for status, 0 = incomplete, 1 = complete, 2 = in delivery, 3 = delivered, 4 = delivery failed


insert into Child values ('Peter Parker', 49.262288, -123.081207, 8, 12, 2939539);
insert into Child values ('Barbara Gordon', 40.7172, -74.0059, 9, 10, 8372959);
insert into Child values ('Harleen Quinzel', 40.7172, -74.0053, 5, 2, 3872533);
insert into Child values ('Barry Allen', 39.3240, -82.0940, 8, 8, 2382753);
insert into Child values ('Richard Grayson', 40.7169, -74.0050, 4, 7, 9873002);
insert into Child values ('Wally West', 39.3247, -82.0937, 5, 8, 1238758);
insert into Child values ('Oliver Queen', 37.4230, -122.1648, 4, 11, 938572);
insert into Child values ('Thea Queen', 37.4230, -122.1648, 6, 4, 53435);

create table Wants (
CID integer, 
iModel integer,
iSerial integer,
primary key(CID, iModel, iSerial),
foreign key (iModel, iSerial) references Item(iModel, iSerial),
foreign key (CID) references Child(CID));

insert into Wants values (2939539,20, 39852);
insert into Wants values (9873002, 852, 432108);
insert into Wants values (9873002, 1, 1);

insert into Wants values (938572, 20, 2953);
insert into Wants values (938572, 76, 16593);
insert into Wants values ( 938572, 58, 82752);
insert into Wants values ( 938572, 1, 1);
insert into Wants values ( 938572, 852, 432108);
insert into Wants values (938572, 32, 65853);
insert into Wants values ( 938572, 6874, 11586);
insert into Wants values ( 938572, 21, 423);
--Oliver Queen is a greedy child 

create table Toy_isFor (
	iModel integer,
	iSerial integer,
	rating integer,
	status integer, 
	sModel integer not null,
	sSerial integer not null,
	CID integer not null unique,
	PRIMARY KEY (iModel, iSerial),
	foreign key (iModel, iSerial) references Item (iModel, iSerial),
	foreign key (sModel, sSerial) references Sleigh (sModel, sSerial),
	foreign key (CID) references Child (CID));	
--	for status, 0 = incomplete, 1 = complete, 2 = in delivery, 3 = delivered, 4 = delivery failed


insert into Toy_isFor values (20, 39852, 8, 0, 9862, 458, 2939539);
insert into Toy_isFor values (76, 16593, 9, 3, 9862, 458, 8372959);
insert into Toy_isFor values (58, 82752, 7, 2, 1960, 135, 3872533);
insert into Toy_isFor values (1, 1, 9, 2, 1960, 120, 2382753);
insert into Toy_isFor values (852, 432108, 5, 1, 1960, 135,1238758);
insert into Toy_isFor values (6874, 2857, 6, 3, 2608, 552,9873002);
insert into Toy_isFor values (21, 423, 3, 0, 2608, 552,938572);

create table ManagerElf(
uname char(40),
pw char(40),
name char(40),
PRIMARY KEY(uname),
foreign key (uname) references Username);

insert into ManagerElf values ('sailor.south', '123asdfjkl', 'Sally South');
insert into ManagerElf values ('smithereeny', 'mypw15','Bob Smith');
insert into ManagerElf values ('compassmaster', 'iblamekanye', 'North West' );
insert into ManagerElf values ('yeastybeasty', 'neverneverland', 'Elise East');
insert into ManagerElf values ('idestroydbs', 'SorryTeacher', 'Bobby Tables');
insert into ManagerElf values ('theotherone', 'bobbyChairs', 'Bobby Tables');

create table UnionWorker(
	Uname char(40),
	pw char(40),
	PRIMARY KEY(Uname),
	foreign key (uname) references Username);

insert into UnionWorker values ('DanceALot', 'hi5hi5hi5');
insert into UnionWorker values ('Coffee5Ever', 'hax0r');
insert into UnionWorker values ('toomuchmusic', 'megaku');
insert into UnionWorker values ('turingMachine', 'crypto');
insert into UnionWorker values ('corgi358', '99bottlesofbeer');

create table FulltimeElf_mng_mon(
	Muname char(40) not null,
	uname char(40),
	pw char(40),
	wages decimal (10, 2),
	insurance decimal (10, 2),
	Uniname char(40) not null,
	name char(40), 
	PRIMARY KEY (uname),
	foreign key (uname) references Username,
	foreign key (Uniname) references UnionWorker (uname),
	foreign key (Muname) references ManagerElf (uname));

insert into FulltimeElf_mng_mon values 
('sailor.south', 'tinkerwinker', 'justameliathings', 18.20, 30.00, 'DanceALot', 'Tinky Winky');
insert into FulltimeElf_mng_mon values
('sailor.south', 'blardigus', 'jjjchocho', 20.50, 45.00, 'toomuchmusic', 'Blarp Bloopus');
insert into FulltimeElf_mng_mon values
('smithereeny', 'spider.nocturne', '8bitarantula', 35.55, 20.00, 'Coffee5Ever', 'Muffy la Arachni');
insert into FulltimeElf_mng_mon values
('compassmaster', 'shoesprayer', 'stevewillneverguessthis', 29.03, 54.86, 'toomuchmusic', 'Flint Lockwood');
insert into FulltimeElf_mng_mon values
('yeastybeasty', 'witchhazel999', 'nine3quarters', 39.02, 28.39, 'turingMachine', 'Hermione Granger');
insert into FulltimeElf_mng_mon values
('idestroydbs', 'lucaluca', 'boxerthehorse', 29.30, 50.20, 'corgi358', 'Lucas Glueface');
insert into FulltimeElf_mng_mon values
('theotherone', 'notarthur', 'rtable', 29.30, 50.20, 'turingMachine', 'Lancelot Glueface');

create table InternElf_train(
	uname char(40),
	pw char(40),
	institution char(40),
	SID integer,
	Funame char(40) not null,
	name char(40),
	duration integer,
	startDate date,
	PRIMARY KEY (uname),
	foreign key (uname) references Username,
	foreign key (Funame) references FulltimeElf_mng_mon(uname),
	CONSTRAINT durationMax CHECK (duration < 13));

insert into InternElf_train values 
('brandonblimp', 'bran8738752', 'UBC', 31244122, 'notarthur', 'Brandon Lim', 3, '2014-2-28');
insert into InternElf_train values 
('yapyap', 'hashtag94', 'UBC', 38343125, 'lucaluca', 'Amelia Yap', 3, '2014-3-25');
insert into InternElf_train values 
('carrotdestroyer', 'fifa14justbc', 'UBC', 53131124, 'witchhazel999', 'Mriganka Arya', 3, '2014-2-10');
insert into InternElf_train values 
('serialpig', 'chicago28', 'UBC', 40626103, 'shoesprayer', 'Maggie Chen', 2, '2014-6-6');
insert into InternElf_train values
('honkytonk3', 'peterpan23', 'UBC', 4639424, 'spider.nocturne', 'Tinky', 4, '2014-5-20');
insert into InternElf_train values
('quinzelqueen', 'mistahjay', 'SUTD', 3756612, 'blardigus', 'Harley', 6, '2014-12-3');
insert into InternElf_train values
('obvileaguer', '39valoran39', 'Arkham', 346235, 'tinkerwinker', 'Demacia', 2, '2014-3-4');

insert into InternElf_train values
('notleaguer', 'alfalffa', 'Arkham', 39857235, 'tinkerwinker', 'Demacia', 2, '2014-10-10');

insert into InternElf_train values
('assassin.of.the.night', 'creed3752', 'Gotham Uni', 324511, 'blardigus', 'Ezio', 1, '2014-5-6');
insert into InternElf_train values
('easterjack2', 'ROTGfr0sty', 'EasterBunny Inst', 2345663, 'spider.nocturne', 'Jack S.', 3 , '2014-4-5');

create table Reindeer_drives(
diet char(40), 
name char(40), 
stall integer, 
sModel integer, 
sSerial integer,
PRIMARY KEY(stall),
foreign key(sModel, sSerial) references Sleigh (sModel, sSerial));

insert into Reindeer_drives values 
('Carrots', 'Mriganka', 30, 9862, 458);
insert into Reindeer_drives values 
('Darkness', 'Batdeer', 4, 5524, 986);
insert into Reindeer_drives values 
('Bamboo', 'Pandeer', 6, 5498, 888);
insert into Reindeer_drives values 
('Cookies', 'Reginald', 7, 1960, 135);
insert into Reindeer_drives values 
('Radioactive sassafras', 'Rudolph', 1, 1960, 120);
insert into Reindeer_drives values 
('Ice cream', 'Peanut', 2, 1960, 120);
insert into Reindeer_drives values 
('Gum', 'Chewy', 25, 2608, 552);
insert into Reindeer_drives values
('Beetles', 'Bugbug', 18, 2608, 552);

create table takeCareOf(
Iuname char(40),
stall integer,
PRIMARY KEY (Iuname, stall),
foreign key (Iuname) references InternElf_train(uname) ON DELETE CASCADE, -- does this need to be conditional...?
foreign key (stall) references Reindeer_drives(stall));

insert into takeCareOf values ('easterjack2', 30);
insert into takeCareOf values ('easterjack2', 4);

insert into takeCareOf values ('assassin.of.the.night', 4);
insert into takeCareOf values ('obvileaguer', 6);
insert into takeCareOf values ('quinzelqueen', 7);
insert into takeCareOf values ('honkytonk3', 1);


CREATE OR REPLACE TRIGGER minimumEmployment
BEFORE DELETE
   ON InternElf_train
for each row
begin
    IF (:old.startdate > sysdate) THEN 
        raise_application_error( -20001, 'You cannot fire an intern until they start work');
    END IF; 
end;

/

