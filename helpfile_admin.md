# Rimba Laras
# Administrator Guide

Version: 1.0

---

# Introduction

Rimba Laras is a configurable ETL (Extract, Transform, Load) platform designed to import, normalize, transform, and load data from external systems into Rimba applications.

Unlike traditional ETL tools that require custom PHP code, Laras provides a fully configurable Filament administration interface that allows administrators to build import pipelines using wizard-driven schema definitions.

A typical data flow follows:

```text
External Source
      │
      ▼
Extract
      │
      ▼
Raw Dataset
      │
      ▼
Transform
      │
      ▼
Normalized Dataset
      │
      ▼
Load
      │
      ▼
Laravel Models
      │
      ▼
Application Database
```

---

# Main Navigation

```text
Admin
└── Laras

    ├── Dashboard
    ├── Data Sources
    ├── Lookup Tables
    ├── Transformation Schemas
    ├── Import Jobs
    ├── Schedules
    ├── Import Runs
    ├── Failed Records
    └── Settings
```

---

# Recommended Setup Sequence

Before running imports, complete the following setup steps.

```text
1. Create Data Source

2. Create Lookup Tables

3. Create Transformation Schema

4. Test Preview

5. Create Import Job

6. Execute Import

7. Configure Schedule
```

---

# Data Sources

## Purpose

A Data Source defines where data originates.

Supported Sources:

```text
Database
API
JSON
CSV
Excel
XML
```

Navigate:

```text
Admin
→ Laras
→ Data Sources
```

---

## Example Database Source

```text
Name:
HRDB Employee Database

Type:
Database

Connection:
employee_db

Table:
employee_master
```

---

## Example API Source

```text
Name:
HR System API

Type:
API

Endpoint:
https://api.company.com/employees

Method:
GET
```

---

# Lookup Tables

## Purpose

Lookup Tables provide centralized value mappings.

Instead of hardcoding conversions into transformation rules, administrators should define them as reusable lookups.

Navigate:

```text
Admin
→ Laras
→ Lookup Tables
```

---

## Example Gender Lookup

```text
Lookup Name:
Gender
```

Mappings:

```text
M → Male
F → Female
```

---

## Example Status Lookup

```text
A → Active
I → Inactive
S → Suspended
```

---

# Transformation Schemas

## Purpose

Transformation Schemas define how source data is converted into destination models.

Navigate:

```text
Admin
→ Laras
→ Transformation Schemas
→ Create
```

Configuration is performed using a seven-step wizard.

---

# Wizard Overview

```text
Step 1
General Information

Step 2
Input Source

Step 3
Record Selection

Step 4
Transformation Rules

Step 5
Output Models

Step 6
Relationships

Step 7
Preview & Validation
```

---

# STEP 1
# General Information

Provide metadata for the schema.

Fields:

```text
Schema Name
Description
Version
Status
Tags
```

Example:

```text
Schema Name:
HRDB Employee Import

Version:
1.0

Status:
Active
```

---

# STEP 2
# Input Source

Select the source previously created.

Fields:

```text
Source
Dataset
Source Key
Batch Size
```

Example:

```text
Source:
HRDB Employee Database

Dataset:
employee_master

Batch Size:
1000
```

The system will attempt to sample records and display detected fields.

Example:

```text
EMP_NO
NAME
SEX
EMAIL
STATUS
JOIN_DATE
```

---

# STEP 3
# Record Selection

Define filters to determine which records are imported.

Available Conditions:

```text
Equals
Not Equals
Contains
Starts With
Ends With
Greater Than
Less Than
In
Not In
Is Null
Not Null
```

Example:

```text
STATUS = ACTIVE

AND

DEPARTMENT = ENGINEERING
```

Only matching records will continue to the transformation stage.

---

# STEP 4
# Transformation Rules

This step defines how source fields are transformed.

A transformation rule contains:

```text
Source Field
Operation
Parameters
Destination Field
Execution Order
```

Multiple rules may be applied to a single field.

---

# Rule Execution

Rules execute from top to bottom.

Example:

```text
Trim

↓
Uppercase

↓
Lookup

↓
Output
```

Order matters.

---

# Supported Rule Types

---

## Rename

Rename a source field.

Source:

```text
EMP_NO
```

Destination:

```text
employee_no
```

Output:

```json
{
  "employee_no": "1001"
}
```

---

## Copy

Duplicate a field.

Source:

```text
FULLNAME
```

Destination:

```text
display_name
```

Output:

```json
{
    "display_name": "John Doe"
}
```

---

## Constant

Assign a fixed value.

Configuration:

```text
Value:
ACTIVE
```

Destination:

```text
status
```

Output:

```json
{
    "status": "ACTIVE"
}
```

---

## Default Value

Only applied when source value is missing.

Configuration:

```text
Default:
ACTIVE
```

---

## Lookup

Use a predefined Lookup Table.

Source:

```text
SEX
```

Lookup:

```text
Gender
```

Output:

```json
{
  "gender": "Male"
}
```

---

## Trim

Removes leading and trailing spaces.

Before:

```text
  John Doe
```

After:

```text
John Doe
```

---

## Uppercase

Before:

```text
John Doe
```

After:

```text
JOHN DOE
```

---

## Lowercase

Before:

```text
JOHN DOE
```

After:

```text
john doe
```

---

## Title Case

Before:

```text
john doe
```

After:

```text
John Doe
```

---

## Replace

Before:

```text
010-1234567
```

Replace:

```text
-
```

With:

```text
(empty)
```

After:

```text
0101234567
```

---

## Split

Split a value into multiple fields.

Source:

```text
John Doe
```

Separator:

```text
Space
```

Output:

```json
{
  "first_name": "John",
  "last_name": "Doe"
}
```

---

## Concatenate

Merge fields.

Inputs:

```text
FIRST_NAME
LAST_NAME
```

Separator:

```text
Space
```

Output:

```json
{
  "full_name": "John Doe"
}
```

---

## Date Format

Convert date formats.

Source:

```text
01/10/2026
```

Output Format:

```text
Y-m-d
```

Result:

```text
2026-10-01
```

---

## Number Format

Normalize numeric values.

Before:

```text
000123
```

After:

```text
123
```

---

## Boolean Conversion

Mappings:

```text
Y → true
N → false
```

---

## JSON Extract

Extract nested values.

Path:

```text
address.city
```

Input:

```json
{
  "address": {
    "city": "Kuala Lumpur"
  }
}
```

Output:

```text
Kuala Lumpur
```

---

## Array Element

Input:

```json
phones[0]
```

Output:

```text
0123456789
```

---

## Expression

Generate calculated values.

Expression:

```text
salary * 12
```

Output:

```text
annual_salary
```

Available Operators:

```text
+
-
*
/
%
()
```

Available Functions:

```text
abs()
round()
ceil()
floor()
min()
max()
```

---

## Conditional

Example:

```text
IF salary > 10000
THEN Senior
ELSE Junior
```

Output:

```text
employee_category
```

---

## Hash

Supported Types:

```text
MD5
SHA1
SHA256
```

Example:

```text
email
```

Produces:

```text
email_hash
```

---

# Rule Groups

Rule Groups allow multiple rules to be chained together.

Example:

```text
FULLNAME

↓

Trim

↓

Title Case

↓

Copy

↓

display_name
```

Result:

```text
John Doe
```

---

# STEP 5
# Output Models

Choose destination models.

Examples:

```text
Employee
User
Agreement
OrgUnit
JobPosition
```

One source record may create multiple outputs.

Example:

```text
Employee
```

Generates:

```text
employees.json
```

Example:

```text
User
```

Generates:

```text
users.json
```

---

# Upsert Configuration

Each output should define a unique key.

Example:

```text
Employee

Upsert Key:
employee_no
```

Behavior:

```text
Exists → Update

Not Exists → Insert
```

---

# STEP 6
# Relationships

Relationships can be created between generated outputs.

Supported Relationships:

```text
Belongs To
Has One
Has Many
Morph One
Morph Many
Belongs To Many
```

---

## Example

Employee

```text
employee_no
```

Agreement

```text
employee_no
```

Relationship:

```text
Agreement belongsTo Employee
```

The loader will resolve associations automatically.

---

# STEP 7
# Preview & Validation

Before saving, administrators should review:

```text
Source Data

↓

Transformed Data

↓

Generated Outputs

↓

Validation Results
```

Preview does not change any database records.

---

# Validation Checks

The system validates:

```text
Required Fields
Lookup Values
Unique Keys
Relationships
Expressions
Date Formats
Destination Models
```

---

# Import Jobs

Navigate:

```text
Admin
→ Laras
→ Import Jobs
```

A Job combines:

```text
Data Source
Transformation Schema
Output Configuration
```

Actions:

```text
Extract

Transform

Load

Run All
```

---

# Scheduling Imports

Navigate:

```text
Admin
→ Laras
→ Schedules
```

Supported Frequencies:

```text
Every Minute
Every 5 Minutes
Hourly
Daily
Weekly
Monthly
Custom Cron
```

Example:

```text
0 2 * * *
```

Meaning:

```text
Run Daily At 02:00
```

---

# Import Runs

Navigate:

```text
Admin
→ Laras
→ Import Runs
```

Available Information:

```text
Started At
Completed At
Duration
Rows Read
Rows Imported
Rows Updated
Rows Failed
Status
```

---

# Failed Records

Navigate:

```text
Admin
→ Laras
→ Failed Records
```

Administrators may:

```text
View Errors
Inspect Raw Data
Inspect Transformed Data
Retry Record
Export Errors
```

---

# Versioning

Every schema should be versioned.

Examples:

```text
HR Import v1.0

HR Import v1.1

HR Import v2.0
```

Recommended Workflow:

```text
Clone Existing Schema

↓

Modify Copy

↓

Test Preview

↓

Activate New Version
```

Never edit a production schema directly.

---

# Recommended Best Practices

✅ Keep extracted data unchanged

✅ Use Lookup Tables whenever possible

✅ Store business logic in transformation rules

✅ Define explicit upsert keys

✅ Preview all changes before deployment

✅ Separate output models

✅ Configure relationships rather than duplicating data

✅ Version schemas before modification

✅ Schedule large imports during off-peak hours

✅ Monitor failed records after every import

---

# Enterprise HR Example

```text
HRDB Employee Database

↓ Extract

employee_master

↓ Transform

Employee
User
Agreement
JobPosition

↓ Load

employees
users
agreements
job_positions
```

This approach allows a single source system to populate multiple Rimba modules while maintaining consistent transformation logic, reusable lookup rules, and auditable import history.