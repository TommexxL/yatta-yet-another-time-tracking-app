
# YATTA - Yet Another Time Tracking App

## 1. Functional Analysis

### Project Goal

Developing a web-based employee time and leave management platform.

The application allows organizations to:

-   Manage employees    
-   Register working hours    
-   Request leave    
-   Report sick leave    
-   Manage schedules    
-   Approve or reject requests    
-   Secure accounts using 2FA    
-   Generate reports
    

The platform must be multi-company ready in the future so it can be sold to different organizations.

----------

### User Roles

#### System Administrator

Responsible for the entire platform.

Permissions:

-   Create users    
-   Edit users    
-   Disable users    
-   Assign roles    
-   Configure company settings    
-   Configure work schedules    
-   Manage holidays    
-   View all reports    
-   Approve/reject requests    
-   Enable 2FA policies
    

----------

#### Manager

Responsible for a department or team.

Permissions:

-   View team members    
-   View submitted requests    
-   Approve/reject leave    
-   View attendance reports
    

----------

#### Employee

Permissions:

-   Login    
-   Manage profile    
-   Enable 2FA    
-   Submit leave requests    
-   Submit sick leave    
-   View own schedule    
-   View own history    
-   View remaining leave balance
    

----------

## Functional Requirements

### Authentication

#### Login

User must be able to:

-   Login with email/employee number and password    
-   Reset password    
-   Logout
    

#### Two Factor Authentication

User must be able to:

-   Enable 2FA    
-   Disable 2FA    
-   Verify login with authenticator app
    

Examples:

-   Google Authenticator    
-   Microsoft Authenticator

##### -> Laravel Fortify

----------

### User Management

System Administrator must be able to:

#### Create User

Required fields:

-   First name    
-   Last name    
-   Email    
-   Phone number    
-   Employee number    
-   Department    
-   Role
    

#### Edit User

Update all employee information.

#### Disable User

Inactive users cannot perform any actions except viewing history.

----------

### Time Registration

Employees must be able to:

#### Clock In

Stores:

-   Date    
-   Start time
    

#### Clock Out

Stores:

-   End time
    

#### Manual Correction Request

Employees can request corrections.

Manager must approve changes.

----------

### Leave Management

#### Leave Request

Employee enters:

-   Start date    
-   End date    
-   Reason    
-   Leave type
    

System calculates:

-   Number of days
    

Statuses:

-   Pending    
-   Approved    
-   Rejected
    

----------

### Sick Leave

Employee enters:

-   Start date    
-   Expected return date    
-   Notes
    

Statuses:

-   Reported    
-   Closed
    

Optional future feature:

-   Doctor certificate upload
    

----------

### Schedule Management

Administrator defines:

#### Standard Schedule

Example:

Monday-Friday
08:00 - 17:00
40h/week

Schedules can later be assigned per employee and manually edited by managers.

----------

### Reporting

Managers and Administrators can view:

-   Worked hours    
-   Sick days    
-   Leave days    
-   Overtime    
-   Absence trends
    

Export:

-   PDF    
-   CSV
    

----------

## Non-Functional Requirements

### Security

    
-   Password hashing    
-   2FA    
-   Audit logging
    

----------

### Performance

System should support:

-   1,000+ users    
-   < 2 second response time
    

----------

### Scalability

Future SaaS support:

-   Multiple companies    
-   Company-specific data    
-   Subscription plans
    

----------

## 2. Technical Preparation

### Technology Stack

Backend:

-   PHP 8.5    
-   Laravel 13
    

Dashboard:

-   Filament
    

Database:

-   MySQL 
    

Authentication:

-   Laravel Breeze    
-   Laravel Fortify (2FA)
    

Authorization:

-   Spatie Laravel Permission
    

Frontend:

-   Livewire    
-   Tailwind CSS   

----------

### Database Design

#### users
id
first_name
last_name
email
phone
employee_number
active
password
company_id
created_at

----------

#### roles

Use Spatie package.

| id |  
| name |

Examples:

-   admin    
-   manager    
-   employee
    

----------

#### schedules

id
name
weekly_hours

----------

#### schedule_days

id
schedule_id
weekday
start_time
end_time

----------

#### time_entries

id
user_id
date
clock_in
clock_out
status

----------

#### leave_requests

id
user_id
leave_type
start_date
end_date
status
reason

----------

#### sick_leaves

id
user_id
start_date
end_date
notes
status

----------

#### audit_logs

id
user_id
action
description
created_at

(Important for commercial software)

----------

## 3. Sprint Backlog

### Sprint 1 - Foundation

#### User Story

As an administrator, I want secure authentication so that only authorized users can access the platform.

Tasks:

-   Create Laravel project   
-   Install Filament    
-   Configure database    
-   Create User model    
-   Install Breeze    
-   Install Fortify    
-   Implement 2FA    
-   Configure roles and permissions
    

Deliverable:

-   Working login system
    

----------

### Sprint 2 - User Management

#### User Story

As an administrator, I want to manage employees.

Tasks:

-   Create user CRUD    
-   Assign roles    
-   Activate/deactivate users    
-   Profile page
    

Deliverable:

-   Full employee management
    

----------

### Sprint 3 - Schedule Management

#### User Story

As an administrator, I want to define work schedules.

Tasks:

-   Create schedules table    
-   Create schedule CRUD    
-   Assign schedules to employees    
-   Validation
    

Deliverable:

-   Schedule management
    

----------

### Sprint 4 - Time Tracking

#### User Story

As an employee, I want to register my worked hours.

Tasks:

-   Create clock-in feature    
-   Create clock-out feature   
-   Time entry history    
-   Daily totals
    

Deliverable:

-   Working time registration
    

----------

### Sprint 5 - Leave Management

#### User Story

As an employee, I want to request leave.

Tasks:

-   Leave request CRUD    
-   Approval workflow    
-   Status management    
-   Notifications
    

Deliverable:

-   Leave request module
    

----------

### Sprint 6 - Sick Leave

#### User Story

As an employee, I want to report sickness.

Tasks:

-   Sick leave registration    
-   Status updates    
-   Manager overview
    

Deliverable:

-   Sick leave module
    

----------

### Sprint 7 - Reporting

#### User Story

As a manager, I want insights into attendance and absences.

Tasks:

-   Dashboard widgets    
-   Attendance reports    
-   Leave reports    
-   Export PDF    
-   Export Excel
    

Deliverable:

-   Reporting module
    

----------

### Sprint 8 - SaaS Readiness

#### User Story

As a software vendor, I want multiple companies to use the platform independently.

Tasks:

-   Add companies table    
-   Multi-tenancy architecture    
-   Company settings    
-   Data isolation    
-   Subscription preparation
    

Deliverable:

-   SaaS-ready architecture
    
----------

### Recommended Architecture for Future Sales

Build the database with a `companies` table from day one:

```
companies
users
schedules
leave_requests
sick_leaves
time_entries
```

Almost every table should contain:

```php
company_id
```

#### This avoids a major rewrite when converting the project into a commercial SaaS product later. This single decision can save weeks of redevelopment.


### Installation: