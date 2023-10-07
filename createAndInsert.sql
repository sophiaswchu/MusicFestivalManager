-- Create tables

CREATE TABLE MusicianPopularity(
	popularity INT,
	expected_turnout INT,
	PRIMARY KEY (popularity)
);

CREATE TABLE StageSize(
	stage_size INT,
	capacity INT,
	PRIMARY KEY (stage_size)
);

CREATE TABLE Venue(
	venue_name VARCHAR2(40),
	city VARCHAR2(40),
	capacity INT,
	accessibility CHAR(80),
	PRIMARY KEY (venue_name)
);

CREATE TABLE Stage(
	venue_name VARCHAR2(40),
	stage_number int,
	stage_size int,
	PRIMARY KEY (venue_name, stage_number),
	FOREIGN KEY (stage_size) REFERENCES StageSize (stage_size) ON DELETE CASCADE,
	FOREIGN KEY (venue_name) REFERENCES Venue (venue_name) ON DELETE CASCADE
);

CREATE TABLE Musician(
	musician_id INT,
	musician_name VARCHAR2(40) NOT NULL,
	festival_year INT,
	stage_venue VARCHAR2(40),
	stage_number INT,
	popularity INT,
	PRIMARY KEY (musician_id),
	FOREIGN KEY (stage_venue, stage_number) REFERENCES Stage(venue_name, stage_number) ON DELETE CASCADE, 
	FOREIGN KEY (popularity) REFERENCES MusicianPopularity
);

CREATE TABLE MarketingPlatform(
	platform VARCHAR2(40),
	content VARCHAR2(40),
	PRIMARY KEY (platform)
);

CREATE TABLE Marketing(
	platform VARCHAR2(40),
	festival_year INT,
	releaseDate VARCHAR2(40),
	PRIMARY KEY (platform, festival_year),
	FOREIGN KEY (platform) REFERENCES MarketingPlatform
);

CREATE TABLE Advertises(
	platform VARCHAR2(40),
	festival_year INT,
	venue_name VARCHAR2(40),
	PRIMARY KEY (platform, festival_year, venue_name),
	FOREIGN KEY (platform, festival_year) REFERENCES Marketing (platform, festival_year),
	FOREIGN KEY (venue_name) REFERENCES Venue (venue_name) ON DELETE CASCADE
);

CREATE TABLE CashFlow (
    id INT,
    quantity INT,
    PRIMARY KEY (id)
);

CREATE TABLE Sponsor(
	sponsor_name VARCHAR2(40),
	festival_year int,
	contributes_to INT, -- changed - incompatible when referencing ID
	PRIMARY KEY (sponsor_name, festival_year),
	FOREIGN KEY (contributes_to) REFERENCES CashFlow (id)
);

CREATE TABLE Features(
	sponsor_name VARCHAR2(40),
	marketing_platform VARCHAR2(40),
	festival_year INT,
	PRIMARY KEY (sponsor_name, marketing_platform, festival_year),
	FOREIGN KEY (sponsor_name, festival_year) REFERENCES Sponsor (sponsor_name, festival_year),
	FOREIGN KEY (marketing_platform, festival_year) REFERENCES Marketing (platform, festival_year)
);


CREATE TABLE VenuePayment( 
    cash_flow_id INT, 
    venue_name VARCHAR2(40),
    PRIMARY KEY (cash_flow_id, venue_name),
    FOREIGN KEY (cash_flow_id) REFERENCES CashFlow, 
    FOREIGN KEY (venue_name) REFERENCES Venue ON DELETE CASCADE
);

CREATE TABLE MusicianPayment(
    id int,
    festival_year int, 
    cash_flow_id int,
    PRIMARY KEY (id, festival_year, cash_flow_id), 
    FOREIGN KEY (id) REFERENCES Musician ON DELETE CASCADE, 
    FOREIGN KEY (cash_flow_id) REFERENCES CashFlow
);

CREATE TABLE MarketingPayment ( 
    platform VARCHAR2(40), 
    festival_year int, 
    cash_flow_id int, 
    PRIMARY KEY (platform, festival_year, cash_flow_id), 
    FOREIGN KEY (platform, festival_year) REFERENCES Marketing, 
    FOREIGN KEY (cash_flow_id) REFERENCES CashFlow
);

CREATE TABLE EmployeePosition( 
    position VARCHAR2(40), 
    hourly_wage int, 
    hours_worked int, 
    PRIMARY KEY (position)
);

CREATE TABLE Employee( 
    employee_id int, 
    position VARCHAR2(40), 
    employee_name VARCHAR2(40) NOT NULL, 
    PRIMARY KEY (employee_id),
    FOREIGN KEY (position) REFERENCES EmployeePosition
);

CREATE TABLE EmployeePayment (
    employee_id int, 
    cash_flow_id int, 
    PRIMARY KEY (employee_id, cash_flow_id), 
    FOREIGN KEY (employee_id) REFERENCES Employee, 
    FOREIGN KEY (cash_flow_id) REFERENCES CashFlow
);

CREATE TABLE Staff(
    id INT, 
    station VARCHAR2(40), 
    PRIMARY KEY (id),
    FOREIGN KEY (id) REFERENCES Employee
);

CREATE TABLE SecurityStaff( 
    id int, 
    license_number int NOT NULL, 
    PRIMARY KEY (id), 
    FOREIGN KEY (id) REFERENCES Employee
);

CREATE TABLE Attendee(
    id int, 
    age int, 
    attendee_name VARCHAR2(40) NOT NULL, 
    PRIMARY KEY (id)
);

CREATE TABLE Ticket(
    ticket_number int, 
    holder int NOT NULL , 
    ticket_to VARCHAR2(40) NOT NULL,
    cash_flow_id int NOT NULL, 
    PRIMARY KEY (ticket_number), 
    FOREIGN KEY (holder) REFERENCES Attendee, 
    FOREIGN KEY (ticket_to) REFERENCES Venue ON DELETE CASCADE,
    FOREIGN KEY (cash_flow_id) REFERENCES CashFlow
);

CREATE TABLE Vendor(
    lot_number int, 
    festival_year int, 
    PRIMARY KEY (lot_number, festival_year)
);

CREATE TABLE FoodVendor(
    lot_number int, 
    festival_year int,
	health_certification int NOT NULL,
	cuisine VARCHAR2(40),
    PRIMARY KEY (lot_number, festival_year)
);

CREATE TABLE DrinkVendor(
	lot_number INT,
	festival_year INT,
	license_id INT NOT NULL,
	drinkType VARCHAR2(40),
	PRIMARY KEY (lot_number, festival_year),
	FOREIGN KEY (lot_number, festival_year) REFERENCES Vendor
);

CREATE TABLE MerchandiseVendor(
	lot_number INT,
	festival_year INT,
	type_sold VARCHAR2(40),
	PRIMARY KEY (lot_number, festival_year),
	FOREIGN KEY (lot_number, festival_year) REFERENCES Vendor
);

CREATE TABLE CustomerReceipt(
	attendee_id INT,
	vendor_lot INT,
	festival_year INT,
	PRIMARY KEY (attendee_id, vendor_lot, festival_year),
	FOREIGN KEY (attendee_id) REFERENCES Attendee,
	FOREIGN KEY (vendor_lot, festival_year) REFERENCES Vendor (lot_number, festival_year)
	);

CREATE TABLE VendorRevenue(
	vendor_lot INT,
	festival_year INT,
	cash_flow_id INT,
	PRIMARY KEY (vendor_lot, festival_year, cash_flow_id),
	FOREIGN KEY (vendor_lot, festival_year) REFERENCES Vendor (lot_number, festival_year),
	FOREIGN KEY (cash_flow_id) REFERENCES CashFlow
);

-- Insert Statements

INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES (1, 10000);
INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES (2, 5000);
INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES (3, 1000);
INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES (4, 500);
INSERT INTO MusicianPopularity (popularity, expected_turnout) VALUES (5, 100);


INSERT INTO StageSize (stage_size, capacity) VALUES (5, 10050);
INSERT INTO StageSize (stage_size, capacity) VALUES (4, 5500);
INSERT INTO StageSize (stage_size, capacity) VALUES (3, 1000);
INSERT INTO StageSize (stage_size, capacity) VALUES (2, 700);
INSERT INTO StageSize (stage_size, capacity) VALUES (1, 100);


INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('O2 Academy', 'London', 10050, 'wheelchair');
INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('Van Concert Hall', 'Vancouver', 10000, 'wheelchair');
INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('UBC Arena', 'Vancouver', 11000, 'no wheelchair');
INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('VIU arena', 'Ninaimo', 2300, 'no wheelchair');
INSERT INTO Venue (venue_name, city, capacity, accessibility) VALUES ('UBC Court', 'Vancouver', 2000, 'wheelchair');

INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('O2 Academy', 1, 5);
INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('Van Concert Hall', 2, 4);
INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('UBC Arena', 3, 3);
INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('VIU arena', 4, 2);
INSERT INTO Stage (venue_name, stage_number, stage_size) VALUES ('UBC Court', 5, 1);

INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES (1,'Taylor Swift', 2020, 'O2 Academy', 1, 1);
INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES (2, 'Drake', 2019, 'Van Concert Hall', 2, 2);
INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES (3, 'Fraxiom', 2020, 'UBC Arena', 3, 3);
INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES (4, 'Alice Gao', 2021, 'VIU arena', 4, 4);
INSERT INTO Musician (musician_id, musician_name, festival_year, stage_venue, stage_number, popularity) VALUES (5, 'Moth and the Streetlights', 2021, 'UBC Court', 5, 5);

INSERT INTO MarketingPlatform (platform, content) VALUES ('Instagram', 'Picture post');
INSERT INTO MarketingPlatform (platform, content) VALUES ('Youtube', 'Promo video');
INSERT INTO MarketingPlatform (platform, content) VALUES ('Blog', 'Text Post');
INSERT INTO MarketingPlatform (platform, content) VALUES ('Facebook', 'Text Post');
INSERT INTO MarketingPlatform (platform, content) VALUES ('Twitter', 'Text Post');

INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('Instagram', 2020, '2022-11-01');
INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('Youtube', 2019, '2021-10-02');
INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('Blog', 2020, '2023-11-08');
INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('Facebook', 2021, '2023-12-03');
INSERT INTO Marketing (platform, festival_year, releaseDate) VALUES ('Twitter', 2021, '2023-10-04');

INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('Instagram', 2020, 'O2 Academy');
INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('Youtube', 2019, 'Van Concert Hall');
INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('Blog', 2020, 'UBC Arena');
INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('Facebook', 2021, 'VIU arena');
INSERT INTO Advertises (platform, festival_year, venue_name) VALUES ('Twitter', 2021, 'UBC Court');


INSERT INTO CashFlow (id, quantity) VALUES (1, 10000);
INSERT INTO CashFlow (id, quantity) VALUES (2, 5000);
INSERT INTO CashFlow (id, quantity) VALUES (3, 1000);
INSERT INTO CashFlow (id, quantity) VALUES (4, 7500);
INSERT INTO CashFlow (id, quantity) VALUES (5, 1500);
INSERT INTO CashFlow (id, quantity) VALUES (6, -1000);
INSERT INTO CashFlow (id, quantity) VALUES (7, -5000);
INSERT INTO CashFlow (id, quantity) VALUES (8, -1000);
INSERT INTO CashFlow (id, quantity) VALUES (9, -500);
INSERT INTO CashFlow (id, quantity) VALUES (10, -100);
INSERT INTO CashFlow (id, quantity) VALUES (11, -10000);
INSERT INTO CashFlow (id, quantity) VALUES (12, -5000);
INSERT INTO CashFlow (id, quantity) VALUES (13, -1000);
INSERT INTO CashFlow (id, quantity) VALUES (14, -500);
INSERT INTO CashFlow (id, quantity) VALUES (15, -100);
INSERT INTO CashFlow (id, quantity) VALUES (16, -10000);
INSERT INTO CashFlow (id, quantity) VALUES (17, -5000);
INSERT INTO CashFlow (id, quantity) VALUES (18, -1000);
INSERT INTO CashFlow (id, quantity) VALUES (19, -500);
INSERT INTO CashFlow (id, quantity) VALUES (20, -101);
INSERT INTO CashFlow (id, quantity) VALUES (21, -800);
INSERT INTO CashFlow (id, quantity) VALUES (22, -300);
INSERT INTO CashFlow (id, quantity) VALUES (23, -1600);
INSERT INTO CashFlow (id, quantity) VALUES (24, -900);
INSERT INTO CashFlow (id, quantity) VALUES (25, -2400);
INSERT INTO CashFlow (id, quantity) VALUES (26, 100);
INSERT INTO CashFlow (id, quantity) VALUES (27, 50);
INSERT INTO CashFlow (id, quantity) VALUES (28, 40);
INSERT INTO CashFlow (id, quantity) VALUES (29, 100);
INSERT INTO CashFlow (id, quantity) VALUES (30, 50);
INSERT INTO CashFlow (id, quantity) VALUES (31, 5);
INSERT INTO CashFlow (id, quantity) VALUES (32, 15);
INSERT INTO CashFlow (id, quantity) VALUES (33, 15);
INSERT INTO CashFlow (id, quantity) VALUES (34, 5);
INSERT INTO CashFlow (id, quantity) VALUES (35, 25);
INSERT INTO CashFlow (id, quantity) VALUES (36, -800);
INSERT INTO CashFlow (id, quantity) VALUES (37, -300);
INSERT INTO CashFlow (id, quantity) VALUES (38, -800);
INSERT INTO CashFlow (id, quantity) VALUES (39, -600);
INSERT INTO CashFlow (id, quantity) VALUES (40, -1600);
INSERT INTO CashFlow (id, quantity) VALUES (41, -300);
INSERT INTO CashFlow (id, quantity) VALUES (42, -125);
INSERT INTO CashFlow (id, quantity) VALUES (43, -250);
INSERT INTO CashFlow (id, quantity) VALUES (44, -800);
INSERT INTO CashFlow (id, quantity) VALUES (45, -400);
INSERT INTO CashFlow (id, quantity) VALUES (46, -125);
INSERT INTO CashFlow (id, quantity) VALUES (47, -800);
INSERT INTO CashFlow (id, quantity) VALUES (48, -600);
INSERT INTO CashFlow (id, quantity) VALUES (49, -400);
INSERT INTO CashFlow (id, quantity) VALUES (50, -800);

INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('Coca Cola', 2020, 1);
INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('Margaret Thatcher Estate', 2019, 2);
INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('Disney', 2020, 3);
INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('UBC', 2021, 4);
INSERT INTO Sponsor (sponsor_name, festival_year, contributes_to) VALUES ('Nintendo', 2021, 5);

INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('Coca Cola', 'Instagram', 2020);
INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('Margaret Thatcher Estate', 'Youtube', 2019);
INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('Disney', 'Blog', 2020);
INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('UBC', 'Facebook', 2021);
INSERT INTO Features (sponsor_name, marketing_platform, festival_year) VALUES ('Nintendo', 'Twitter', 2021);

INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES (6, 'O2 Academy');
INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES (7, 'Van Concert Hall');
INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES (8, 'UBC Arena');
INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES (9, 'VIU arena');
INSERT INTO VenuePayment (cash_flow_id, venue_name) VALUES (10, 'UBC Court');

INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES (11, 1, 2020);
INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES (12, 2, 2019);
INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES (13, 3, 2020);
INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES (14, 4, 2021);
INSERT INTO MusicianPayment (cash_flow_id, id, festival_year) VALUES (15, 5, 2021);

INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('Instagram', 2020, 16);
INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('Youtube', 2019, 17);
INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('Blog', 2020, 18);
INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('Facebook', 2021, 19);
INSERT INTO MarketingPayment (platform, festival_year, cash_flow_id) VALUES ('Twitter', 2021, 20);

INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('Full-Time', 20, 40);
INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('Part-Time', 15, 20); 
INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('Volunteer', 0, 15);
INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('Casual-Contract', 25, 5); 
INSERT INTO EmployeePosition (position, hourly_wage, hours_worked) VALUES ('Freelance-Contract', 40, 10);

INSERT INTO Employee (employee_id, position, employee_name) VALUES (1, 'Full-Time', 'Jane Doe');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (2, 'Part-Time', 'Bruce Wayne');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (3, 'Full-Time', 'John Smith');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (4, 'Part-Time', 'Clark Kent');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (5, 'Full-Time', 'Lois Lane');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (6, 'Full-Time', 'Tony Stark');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (7, 'Part-Time', 'Stephen Strange');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (8, 'Full-Time', 'Bruce Banner');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (9, 'Part-Time', 'Gerald Guy');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (10, 'Full-Time', 'Bucky Barnes');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (11, 'Volunteer', 'Arthur Currie');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (12, 'Casual-Contract', 'Kate Kane');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (13, 'Casual-Contract', 'Victor Stone');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (14, 'Volunteer', 'John Stewart');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (15, 'Freelance-Contract', 'Carter Hall');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (16, 'Casual-Contract', 'Rachel Roth');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (17, 'Freelance-Contract', 'Ted Knight');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (18, 'Volunteer', 'Donna Troy');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (19, 'Freelance-Contract', 'Diana Prince');
INSERT INTO Employee (employee_id, position, employee_name) VALUES (20, 'Volunteer', 'Jessica Drew');

INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (1, 21);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (2, 22);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (3, 23);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (4, 24);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (5, 25);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (6, 36);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (7, 37);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (8, 38);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (9, 39);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (10, 40);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (7, 41);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (12, 42);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (13, 43);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (1, 44);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (15, 45);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (16, 46);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (17, 47);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (4, 48);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (19, 49);
INSERT INTO EmployeePayment (employee_id, cash_flow_id) VALUES (10, 50);

INSERT INTO Staff (id, station) VALUES (1, 'Arena Front');
INSERT INTO Staff (id, station) VALUES (2, 'Arena Back');
INSERT INTO Staff (id, station) VALUES (3, 'Arena Front');
INSERT INTO Staff (id, station) VALUES (4, 'Ticketing');
INSERT INTO Staff (id, station) VALUES (5, 'Ticketing');
INSERT INTO Staff (id, station) VALUES (11, 'Attendee Assistance');
INSERT INTO Staff (id, station) VALUES (12, 'Waste Removal');
INSERT INTO Staff (id, station) VALUES (13, 'Stage Setup');
INSERT INTO Staff (id, station) VALUES (14, 'Sponsorship Advertisement');
INSERT INTO Staff (id, station) VALUES (15, 'Electrical Maintenance');
INSERT INTO Staff (id, station) VALUES (16, 'Marketing Consult');
INSERT INTO Staff (id, station) VALUES (17, 'Stage Stability Assessment');
INSERT INTO Staff (id, station) VALUES (18, 'Arena Front');
INSERT INTO Staff (id, station) VALUES (20, 'Ticketing');

INSERT INTO SecurityStaff (id, license_number) VALUES (6, 1);
INSERT INTO SecurityStaff (id, license_number) VALUES (7, 2);
INSERT INTO SecurityStaff (id, license_number) VALUES (8, 3);
INSERT INTO SecurityStaff (id, license_number) VALUES (9, 4);
INSERT INTO SecurityStaff (id, license_number) VALUES (10, 5);
INSERT INTO SecurityStaff (id, license_number) VALUES (19, 6);

INSERT INTO Attendee (id, age, attendee_name) VALUES (1, 23, 'Jonnes Ocean');
INSERT INTO Attendee (id, age, attendee_name) VALUES (2, 29, 'Damien River');
INSERT INTO Attendee (id, age, attendee_name) VALUES (3, 22, 'Mandi Moore');
INSERT INTO Attendee (id, age, attendee_name) VALUES (4, 40, 'Jane Lakes');
INSERT INTO Attendee (id, age, attendee_name) VALUES (5, 26, 'Rupert Giles');

INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES (1, 1, 'O2 Academy', 26);
INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES (2, 2, 'Van Concert Hall', 27);
INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES (3, 3, 'UBC Arena', 28);
INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES (4, 4, 'VIU arena', 29);
INSERT INTO Ticket (ticket_number, holder, ticket_to, cash_flow_id) VALUES (5, 5, 'UBC Court', 30);

INSERT INTO Vendor (lot_number, festival_year) VALUES (1, 2019);
INSERT INTO Vendor (lot_number, festival_year) VALUES (2, 2019);
INSERT INTO Vendor (lot_number, festival_year) VALUES (3, 2019);
INSERT INTO Vendor (lot_number, festival_year) VALUES (4, 2019);
INSERT INTO Vendor (lot_number, festival_year) VALUES (5, 2019);
INSERT INTO Vendor (lot_number, festival_year) VALUES (1, 2020);
INSERT INTO Vendor (lot_number, festival_year) VALUES (2, 2020);
INSERT INTO Vendor (lot_number, festival_year) VALUES (3, 2020);
INSERT INTO Vendor (lot_number, festival_year) VALUES (4, 2020);
INSERT INTO Vendor (lot_number, festival_year) VALUES (5, 2020);
INSERT INTO Vendor (lot_number, festival_year) VALUES (1, 2021);
INSERT INTO Vendor (lot_number, festival_year) VALUES (2, 2021);
INSERT INTO Vendor (lot_number, festival_year) VALUES (3, 2021);
INSERT INTO Vendor (lot_number, festival_year) VALUES (4, 2021);
INSERT INTO Vendor (lot_number, festival_year) VALUES (5, 2021);

INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (1, 2019, 111, 'Chinese');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (2, 2019, 222, 'Japanese');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (3, 2019, 333, 'Korean');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (4, 2019, 444, 'Italian');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (5, 2019, 555, 'Mexican');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (1, 2020, 666, 'Chinese');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (2, 2020, 777, 'Japanese');
INSERT INTO FoodVendor (lot_number, festival_year, health_certification, cuisine) VALUES (3, 2020, 888, 'Korean');

INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES (1, 2020, 6661, 'Lemonade');
INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES (2, 2020, 7771, 'Beer');
INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES (3, 2020, 8882, 'Alcoholic Beverages');
INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES (4, 2020, 9993, 'Beer');
INSERT INTO DrinkVendor (lot_number, festival_year, license_id, drinkType) VALUES (5, 2020, 1041, 'Lemonade');

INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (2, 2020, 'T-Shirts');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (3, 2020, 'Toys');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (4, 2020, 'T-Shirts');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (5, 2020, 'Toys');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (1, 2021, 'T-Shirts');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (2, 2021, 'Toys');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (3, 2021, 'Records');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (4, 2021, 'T-Shirts');
INSERT INTO MerchandiseVendor (lot_number, festival_year, type_sold) VALUES (5, 2021, 'Key-chains');

INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (1, 1, 2019);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (2, 1, 2019);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (1, 2, 2019);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (4, 3, 2019);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (5, 4, 2019);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (1, 3, 2020);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (1, 4, 2021);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (1, 5, 2020);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (2, 2, 2020);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (2, 3, 2020);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (2, 4, 2021);
INSERT INTO CustomerReceipt (attendee_id, vendor_lot, festival_year) VALUES (2, 5, 2021);

INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES (1, 2019, 31);
INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES (1, 2019, 32);
INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES (2, 2019, 33);
INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES (3, 2019, 34);
INSERT INTO VendorRevenue (vendor_lot, festival_year, cash_flow_id) VALUES (4, 2019, 35);
