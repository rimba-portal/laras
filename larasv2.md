# Rimba Laras

> Lightweight ETL package for the Rimba ecosystem.
>
> Laras converts external data into Seed JSON files and delegates persistence to `JsonSeedThruModel` from `rimba/asas`.

---

# Philosophy

Traditional ETL frameworks often become difficult to maintain because they introduce:

- Mapping languages
- Expression engines
- Inline PHP
- SQL lookups
- Transformation DSLs
- Custom persistence layers

Over time the configuration becomes more complicated than the code.

Laras intentionally avoids this.

## Laras Rules

### JSON is Data

```text
raw.json
staff.json
job_positions.json
agreements.json
```

### PHP is Engine

```text
DatabaseExtractor
ApiExtractor
TransformationEngine
JsonSeedThruModel
```

### Models Own Database Knowledge

```php
Staff::seedMappings()
Agreement::seedMappings()
JobPosition::seedMappings()
```

Laras never resolves foreign keys.

Laras never performs model lookups.

Laras only shapes data.

---

# High Level Flow

```text
External Database
API
CSV
Excel
LDAP

        │
        ▼

    Extractor

        │
        ▼

      raw.json

        │
        ▼

Transformation Definition

        │
        ▼

 Seed JSON Files

        │
        ▼

 JsonSeedThruModel

        │
        ▼

     Database
```

---

# Runtime Flow

```text
Pipeline
    │
    ▼

Extractor

    │
    ▼

raw.json

    │
    ▼

Transformation Engine

    │
    ▼

staff.json
agreements.json
job_positions.json

    │
    ▼

JsonSeedThruModel

    │
    ▼

Database
```

---

# Dependency

Laras depends on:

```text
rimba/asas
```

Specifically:

```php
JsonSeedThruModel
GetModelInfo
BitesServiceProvider
```

---

# Package Responsibilities

## Laras

Responsible for:

```text
Extract
Transform
Generate Seed JSON
Run JsonSeedThruModel
```

---

## Asas

Responsible for:

```text
Create Records
Update Records
Unique Resolution
Relationship Handling
seedMappings()
```

---

# Non Goals

Laras is NOT:

```text
Workflow Engine
Message Bus
Change Tracking System
Snapshot System
Audit Engine
Event Sourcing Framework
```

Laras only does:

```text
Extract
Transform
Seed
```

---

# Pipeline Definition

Each pipeline contains:

```php
name

extractor_class

extractor_config

transformation_definition

depends_on

active
```

---

# Example Pipeline

```php
HRDB Employees
```

---

## Raw Extracted Json

File:

```text
raw.json
```

Contents:

```json
[
    {
        "uuid": "EMP-001",
        "workcode": "100001",
        "lastname": "John Tan",

        "organization_uuid": "ORG-ATM",

        "department_uuid": "DEPT-IT",

        "job_title_name":
            "E3-SENIOR ENGINEER-IT",

        "manager_uuid":
            "EMP-100",

        "companystartdate":
            "2020-01-01",

        "workenddate":
            null,

        "field3":
            "E3",

        "field4":
            "CC100",

        "field8":
            "SHIFT-A"
    }
]
```

---

# Transformation Definition

File:

```text
transformation.json
```

---

## Complete Example

```json
{
    "outputs": [

        {
            "file": "staff.json",

            "table": "staff",

            "fields": {

                "uuid": {
                    "from": "uuid"
                },

                "staff_no": {
                    "from": "workcode"
                },

                "name": {
                    "from": "lastname"
                },

                "type": {
                    "value": "FTE"
                },

                "org_corp_uuid": {
                    "from":
                        "organization_uuid"
                },

                "org_unit_uuid": {
                    "from":
                        "department_uuid"
                },

                "shift_code": {
                    "from":
                        "field8"
                },

                "paygrade": {
                    "from":
                        "field3"
                },

                "cost_center": {
                    "from":
                        "field4"
                }
            }
        },

        {
            "file":
                "job_positions.json",

            "table":
                "job_positions",

            "fields": {

                "uuid": {
                    "from":
                        "uuid"
                },

                "job_title_name": {
                    "from":
                        "job_title_name"
                },

                "reports_to_uuid": {
                    "from":
                        "manager_uuid"
                },

                "org_unit_uuid": {
                    "from":
                        "department_uuid"
                }
            }
        },

        {
            "file":
                "agreements.json",

            "table":
                "agreements",

            "fields": {

                "source_uuid": {
                    "from":
                        "uuid"
                },

                "agreement_type_code": {
                    "value":
                        "staff_contract"
                },

                "title_prefix": {
                    "value":
                        "Staff Contract - "
                },

                "staff_no": {
                    "from":
                        "workcode"
                },

                "party_a_uuid": {
                    "from":
                        "organization_uuid"
                },

                "start_date": {
                    "from":
                        "companystartdate"
                },

                "end_date": {
                    "from":
                        "workenddate"
                },

                "status": {
                    "value":
                        "Active"
                }
            }
        }
    ]
}
```

---

# Generated Output

---

## staff.json

```json
{
    "staff": [

        {
            "uuid":
                "EMP-001",

            "staff_no":
                "100001",

            "name":
                "John Tan",

            "type":
                "FTE",

            "org_corp_uuid":
                "ORG-ATM",

            "org_unit_uuid":
                "DEPT-IT",

            "shift_code":
                "SHIFT-A",

            "paygrade":
                "E3",

            "cost_center":
                "CC100"
        }

    ]
}
```

---

## job_positions.json

```json
{
    "job_positions": [

        {
            "uuid":
                "EMP-001",

            "job_title_name":
                "E3-SENIOR ENGINEER-IT",

            "reports_to_uuid":
                "EMP-100",

            "org_unit_uuid":
                "DEPT-IT"
        }

    ]
}
```

---

## agreements.json

```json
{
    "agreements": [

        {
            "source_uuid":
                "EMP-001",

            "agreement_type_code":
                "staff_contract",

            "title_prefix":
                "Staff Contract - ",

            "staff_no":
                "100001",

            "party_a_uuid":
                "ORG-ATM",

            "start_date":
                "2020-01-01",

            "end_date":
                null,

            "status":
                "Active"
        }

    ]
}
```

---

# Relationship Support

Laras supports relationships because `JsonSeedThruModel` already supports relationships.

---

## Transformation Definition

```json
{
    "outputs": [

        {
            "table": "staff",

            "fields": {

                "uuid": {
                    "from": "uuid"
                },

                "staff_no": {
                    "from": "workcode"
                }
            },

            "relations": {

                "agreements": {

                    "fields": {

                        "uuid": {
                            "from":
                                "agreement_uuid"
                        },

                        "title": {
                            "from":
                                "agreement_title"
                        }
                    },

                    "relations": {

                        "scopes": {

                            "fields": {

                                "scopeable_uuid": {

                                    "from":
                                        "uuid"

                                }

                            }
                        }
                    }
                }
            }
        }
    ]
}
```

---

# Generated Seed JSON

```json
{
    "staff": [

        {

            "uuid":
                "EMP-001",

            "staff_no":
                "100001",

            "agreements": [

                {

                    "uuid":
                        "AGR-001",

                    "title":
                        "Staff Contract",

                    "scopes": [

                        {

                            "scopeable_uuid":
                                "EMP-001"

                        }

                    ]
                }

            ]
        }

    ]
}
```

---

# Database Resolution

Laras never resolves:

```text
org_unit_id
job_position_id
staff_id
agreement_id
```

Those belong to:

```php
seedMappings()
```

---

## Example

```php
public static function seedMappings(): array
{
    return [

        'org_unit_uuid'
            => fn ($uuid) => [

                'org_unit_id' =>
                    OrgUnit::query()
                        ->where(
                            'uuid',
                            $uuid
                        )
                        ->value('id')

            ],

    ];
}
```

---

# Supported Field Types

## Copy

```json
{
    "name": {
        "from": "lastname"
    }
}
```

---

## Constant

```json
{
    "type": {
        "value": "FTE"
    }
}
```

---

## Lookup

```json
{
    "gender": {

        "from": "sex",

        "lookup": {

            "1": "F",
            "F": "F",

            "0": "M",
            "M": "M"
        }
    }
}
```

---

## Default

```json
{
    "status": {

        "from": "status",

        "default": "Active"
    }
}
```

---

# Pipeline Dependencies

Pipelines can depend on earlier pipelines.

Example:

```text
Organizations
        ↓

Departments
        ↓

Job Titles
        ↓

Employees
```

Pipeline:

```json
{
    "depends_on": [

        "HRDB Organizations",

        "HRDB Departments",

        "HRDB Job Titles"

    ]
}
```

---

# Supported Extractors

## Database

```php
DatabaseExtractor
```

---

## REST API

```php
ApiExtractor
```

---

## CSV

```php
CsvExtractor
```

---

## Excel

```php
ExcelExtractor
```

---

## Json

```php
JsonExtractor
```

---

# Storage Structure

```text
storage/laras/

├── hrdb-organizations/
│   ├── raw.json
│   └── org_corps.json
│
├── hrdb-departments/
│   ├── raw.json
│   └── org_units.json
│
├── hrdb-employees/
│   ├── raw.json
│   ├── staff.json
│   ├── job_positions.json
│   ├── agreements.json
│   └── agreement_scopes.json
```

---

# Package File Tree

```text
rimba/laras

config/
└── laras.php

database/
└── migrations/
    └── create_laras_pipelines_table.php

src/

├── LarasServiceProvider.php
│
├── Contracts/
│   ├── Extractor.php
│   └── Transformer.php
│
├── Models/
│   └── Pipeline.php
│
├── Actions/
│   ├── RunPipeline.php
│   ├── ExtractData.php
│   ├── TransformData.php
│   └── SeedData.php
│
├── Extractors/
│   ├── DatabaseExtractor.php
│   ├── ApiExtractor.php
│   ├── CsvExtractor.php
│   ├── ExcelExtractor.php
│   └── JsonExtractor.php
│
├── Services/
│   ├── PipelineRunner.php
│   ├── TransformationEngine.php
│   └── OutputFileBuilder.php
│
├── Support/
│   ├── JsonArtifact.php
│   ├── PipelineResult.php
│   └── TransformationContext.php
│
├── Console/
│   ├── RunPipelineCommand.php
│   ├── ExtractPipelineCommand.php
│   ├── TransformPipelineCommand.php
│   └── SeedPipelineCommand.php
│
└── Http/
    └── UI/
        └── Admin/
            └── Resources/
                └── Pipelines/
                    ├── PipelineResource.php
                    ├── Pages/
                    ├── Tables/
                    └── Schemas/

README.md
```

---

# Summary

Laras is intentionally small.

```text
Extract
    ↓
Transform
    ↓
Seed JSON
    ↓
JsonSeedThruModel
    ↓
Database
```

Everything related to:

```text
Model Resolution
Relationship Resolution
Foreign Key Resolution
Persistence
```

belongs to `rimba/asas`.

Everything related to:

```text
External Data
Transformation Definitions
Seed File Generation
```

belongs to `rimba/laras`.