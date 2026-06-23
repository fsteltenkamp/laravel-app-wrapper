# Starter Application

Simple starter application that can be used as a base for any application.

## Core Concepts

### Models

- User
- Permission
- Role
- Team

#### Lists

- Lists should be a simple searchable Table with a create button and pagination.
- There should be simple actions on every row like "edit" and "delete" when the correct permissions are present.
- If The model the list is showing has a "view" element instead of or additional to the "edit" element, the whole row (except the actions) should be clickable and redirect to that view.
- The "Create" Button should show a Modal (also only with permissions present) that fetches the required parameters for the creation of one of the models, through simple text fields.
- The "Edit" should be a separate page to allow for more sophisticated html elements.

#### Edit

- Simple Page with cards that handle different properties of the model.
- Updating needs a permission check of course.
