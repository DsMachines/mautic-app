# Cursor Rules Setup Guide - WordPress Plugin Development

## 🎯 Overview

This guide explains the comprehensive Cursor rules organization for your WordPress plugins (Dashboard Plugin v1.0.4 and CPaaS Plugin). The structure is designed to provide context-aware assistance that automatically references the appropriate rules based on your development context.

## 📁 Rules Structure

```
plugins/
├── .cursorrules                           # Main rules (WordPress + MCP integration)
├── docs/
│   ├── shared/
│   │   └── cursor-specialized-rules/      # Specialized rules for specific contexts
│   │       ├── context7-integration.md   # Context7 MCP patterns & templates
│   │       ├── browsertools-workflow.md  # Browser Tools debugging workflows
│   │       └── mcp-development-workflow.md # Combined MCP development patterns
│   ├── Dashboard/
│   │   ├── dashboard-project-rules.md     # Dashboard plugin specific rules
│   │   ├── dashboard-cursorrules.md       # Original dashboard rules
│   │   └── implementation-plan.md         # Implementation plan
│   └── CPaaS/
│       └── cpaas-project-rules.md         # CPaaS plugin specific rules
```

## 🔧 How Cursor Uses These Rules

### General Development (.cursorrules)
**When**: All WordPress plugin development tasks  
**Contains**: 
- WordPress best practices from PMPro/Defender analysis
- Context7 & Browser Tools MCP integration patterns
- Security, performance, and database patterns
- Modern PHP 8.3 features
- CPaaS integration patterns

### Specialized Rules (docs/shared/cursor-specialized-rules/)

#### Context7 Integration Rules
**When**: Need documentation or research during development  
**Contains**:
- Ready-to-use Context7 commands
- Token-optimized request templates
- Development phase-specific queries
- Debugging and troubleshooting templates

#### Browser Tools Workflow Rules  
**When**: Frontend debugging, design capture, performance testing  
**Contains**:
- GoHighLevel design capture workflows
- JavaScript/AJAX debugging commands
- Performance optimization workflows
- Security testing patterns

#### MCP Development Workflow Rules
**When**: Complex development sessions requiring both MCPs  
**Contains**:
- Dual MCP usage patterns
- Development phase workflows
- Efficiency optimization strategies
- Common scenario solutions

### Project-Specific Rules

#### Dashboard Plugin Rules
**When**: Working on Dashboard plugin features  
**Contains**:
- GoHighLevel design requirements
- Container system architecture
- Canvas builder specifications
- CPaaS integration patterns

#### CPaaS Plugin Rules
**When**: Working on CPaaS plugin features  
**Contains**:
- Shortcode development patterns
- Supabase integration requirements
- n8n workflow integration
- Dashboard plugin integration

## 🚀 Usage Instructions

### For AI Assistants

#### Step 1: Always Start with General Rules
Reference `.cursorrules` for foundational WordPress development patterns.

#### Step 2: Use Specialized Rules When Needed
- **Need documentation?** → Reference `context7-integration.md`
- **Debugging frontend issues?** → Reference `browsertools-workflow.md` 
- **Complex development session?** → Reference `mcp-development-workflow.md`

#### Step 3: Apply Project-Specific Rules
- **Dashboard plugin work?** → Reference `dashboard-project-rules.md`
- **CPaaS plugin work?** → Reference `cpaas-project-rules.md`

### For Developers

#### Daily Development Workflow

**Morning Setup**:
1. Review project-specific rules for your current plugin
2. Start MCP servers (Context7 + Browser Tools)
3. Reference relevant specialized rules

**Development Session**:
1. Use Context7 patterns from `context7-integration.md`
2. Debug with Browser Tools patterns from `browsertools-workflow.md`
3. Follow MCP workflows from `mcp-development-workflow.md`

**Feature Development**:
1. Check project-specific requirements
2. Follow security and performance patterns from `.cursorrules`
3. Use appropriate MCP integration patterns

## 🔧 MCP Server Setup

### Context7 MCP
```bash
# Automatically configured in Cursor
# No manual setup required
```

### Browser Tools MCP
```bash
# Terminal/PowerShell
npx @agentdeskai/browser-tools-server@latest
```

### Verify Setup
Test both servers in Cursor chat:
```bash
# Test Context7
"Get WordPress plugin development documentation"

# Test Browser Tools  
"Show console logs"
```

## 📊 Efficiency Benefits

### Time Savings
- **Traditional Development**: 60-90 minutes per feature
- **With MCP Rules**: 32-48 minutes per feature
- **Estimated Savings**: 45-50% reduction in development time

### Quality Improvements
- Consistent WordPress coding standards
- Automated security pattern implementation
- Performance optimization built-in
- Comprehensive testing workflows

### Development Experience
- Context-aware assistance
- Reduced documentation search time
- Streamlined debugging workflows
- Automated best practice enforcement

## 🎯 Quick Reference

### Common Development Scenarios

#### Scenario 1: New WordPress Plugin Feature
1. **Start**: Reference `.cursorrules` for WordPress patterns
2. **Research**: Use Context7 patterns from specialized rules
3. **Develop**: Follow security and performance guidelines
4. **Debug**: Use Browser Tools workflows
5. **Test**: Follow testing checklists from project rules

#### Scenario 2: GoHighLevel UI Implementation
1. **Design Capture**: Use Browser Tools workflow for design analysis
2. **Research**: Use Context7 for CSS/JavaScript documentation
3. **Implement**: Follow Dashboard plugin rules
4. **Test**: Use Browser Tools for responsive testing

#### Scenario 3: CPaaS Integration
1. **Architecture**: Reference CPaaS plugin rules
2. **Security**: Follow .cursorrules security patterns
3. **Integration**: Use MCP workflow for API integration
4. **Testing**: Use Browser Tools for end-to-end testing

### Emergency Debugging
If something isn't working:
1. Check Browser Tools for console errors
2. Use Context7 for troubleshooting documentation  
3. Reference MCP workflow for systematic debugging
4. Check project rules for specific requirements

## 📋 Maintenance

### Regular Updates
- Update .cursorrules when WordPress/PHP versions change
- Refresh Context7 templates when new APIs are added
- Update Browser Tools workflows when new debugging features are available
- Maintain project rules as features evolve

### Rule Validation
- Test Context7 patterns regularly
- Verify Browser Tools commands work correctly
- Ensure MCP workflows are efficient
- Update project rules based on development experience

---

**Success Metrics**: Your development should be faster, more consistent, and higher quality when following this rules structure. The combination of general WordPress best practices with specialized MCP patterns and project-specific requirements ensures comprehensive development support. 