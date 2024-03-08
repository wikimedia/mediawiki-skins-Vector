# 4. Code sharing between Vector and Minerva Skins

Date: 2024-03-07

## Status

In Progress.

## Context

Exploring effective strategies for code sharing between Vector and Minerva Skins to reduce duplication and improve maintenance. Key options considered include creating a Composer library for shared functionalities, duplicating code in both skins, and moving shared code to the MediaWiki core.

## Decision

After thorough consideration, the following options are under evaluation for code sharing between Vector and Minerva Skins:
- **Creating a Composer Library**: Centralizing shared functionalities to reduce duplication and improve update management.
- **Duplicating Code**: Implementing similar functionalities independently in both skins, prioritizing flexibility and skin-specific optimizations.
- **Moving Shared Code to MediaWiki Core**: Leveraging the core platform to provide shared functionalities, enhancing accessibility and consistency across skins.

Each option is assessed for its potential impact on development efficiency, maintenance, and the ability to support future enhancements.
## Consequences

The chosen strategy will significantly impact the development workflow, codebase maintainability, and potential for feature consistency across skins. A careful assessment is underway to ensure the selected approach aligns with long-term objectives for scalability and efficiency in the MediaWiki ecosystem.
