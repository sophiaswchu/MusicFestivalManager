# MusicFestivalManager

## Description 

MusicFestivalDBGUI.php presents a way for music festival organizers to view, edit and query their year-to-year data. This allows them to have better planning for future years by providing insight into trends, and an accumulation of all data for reference. 

Our largest and most detailed table is CashFlow, allowing the organizers to keep track of the incoming and outgoing monetary transactions. After all, festival organization is a business, and we want to maximize profit. CashFlow is connected to all entities that contribute to the overall money-making success of the festival such as musicians, marketing, venue, sponsors, attendees and tickets, and vendors. We have allowed the values to be positive for money coming in and negative for money going out.

Our proudest implementation is the default page, “Custom Query”. This allows our organizer to get specifics from any of the tables, choosing any amount of attributes and being able to specify any amount of conditions. We chose to have these conditions connected by AND so that the organizer can really zoom in on a particular item or group of items rather than another conjunction like OR that might combine items/groups that have different conditions. 

## Schema 
<img src="https://github.com/sophiaswchu/MusicFestivalManager/assets/94317776/82a20341-5aa0-4676-ba0e-d315c7e55e76" width="550">


## Queries
- SELECT attributes FROM table WHERE attributes connected by AND
[>|>=|=|<>|<=|<|LIKE] user-input
  - See Lines: 46-143
- SELECT * FROM table
- INSERT newItem INTO table
  - See Lines: 443-448 (Single example)
- DELETE FROM Venue WHERE venue_name = user-input
  - See Lines: 353-357
- UPDATE FoodVendor SET drop-down = user-input WHERE drop-down = user-input
  - See Lines: 358-367
- SELECT attendee_name, ticket_to FROM Attendee INNER JOIN Ticket ON Attendee.id = Ticket.holder WHERE age > user-input
  - See Lines: 383-389
- SELECT position, ABS(ROUND(AVG(quantity), 2)) AS Average_Salary FROM (Employee INNER JOIN EmployeePayment WHERE Employee.employee_id = EmployeePayment.employee_id) INNER JOIN CashFlow where EmployeePayment.cash_flow_id = CashFlow.id GROUP BY position
  - See Lines: 390-396
- SELECT ticket_to AS venue_name, SUM(quantity) AS ticket_sales FROM Ticket INNER JOIN CashFlow ON Ticket.cash_flow_id = CashFlow.id GROUP BY ticket_to HAVING SUM(quantity) > user-input
  - See Lines: 397-403
- SELECT cuisine, AVG(sumval) AS avg_sumval FROM (SELECT FoodVendor.festival_year, cuisine, SUM(CashFlow.quantity) AS sumval FROM FoodVendor INNER JOIN VendorRevenue ON FoodVendor.lot_number = VendorRevenue.vendor_lot INNER JOIN CashFlow ON VendorRevenue.cash_flow_id = CashFlow.id GROUP BY FoodVendor.festival_year, cuisine) GROUP BY cuisine
  - See Lines: 404-410
- SELECT attendee_name FROM Attendee WHERE NOT EXISTS ((SELECT lot_number FROM Vendor) MINUS (SELECT vendor_lot FROM CustomerReceipt WHERE attendee_id = id))
  - See Lines: 339-343

## Examples 
### Selection 
<img src="https://github.com/sophiaswchu/MusicFestivalManager/assets/94317776/940b1439-e957-489a-94b0-a9f49d342600" width="550">

### Projection
<img src="https://github.com/sophiaswchu/MusicFestivalManager/assets/94317776/fa0796dc-38d3-4e3d-8a6c-c25603eea5d1" width="550">

