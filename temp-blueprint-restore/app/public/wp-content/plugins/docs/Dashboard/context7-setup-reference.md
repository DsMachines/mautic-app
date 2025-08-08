# Context7 Setup & Reference Guide - Dashboard Plugin

## 🏢 Project Overview & Business Context

**What We're Building**: Enterprise-grade CPaaS (Communications Platform as a Service) dashboard plugin for WordPress that replicates GoHighLevel's interface and functionality.

**Business Problem Solved**: 
- PMPro members need personalized communication management dashboards
- Users require real-time interaction with Twilio, Supabase, and n8n workflows
- Enterprise clients need secure, scalable communication automation tools
- Shared hosting users need optimized performance without sacrificing functionality

**Your Role**: Lead developer implementing Phase 2 of Dashboard Plugin development  
**Current Status**: Dashboard Plugin v1.0.4 with basic navigation and security complete  
**Phase 2 Goal**: Template-based system with real-time API integrations (5-week implementation)

**Critical Success Factors**:
- **Shared Hosting Compatible**: Must work efficiently on budget hosting environments
- **Subdomain Deployment**: Plugin must function on subdomains like `dashboard.yourdomain.com`
- **PMPro Integration**: Seamless membership level control required
- **Mobile-First**: Touch-friendly responsive design mandatory
- **Enterprise Security**: Multi-layer security with encrypted credentials
- **Performance Budget**: Page load under 3 seconds, real-time updates under 500ms

---

## 🔧 Current Technical Environment & Setup

**Existing Infrastructure (DO NOT MODIFY)**:
- **WordPress 6.8+** with Dashboard Plugin v1.0.4 ACTIVE and FUNCTIONAL
- **Database Schema**: 5 tables created (`dashboard_menus`, `dashboard_menu_items`, `dashboard_containers`, `dashboard_canvas`, `dashboard_user_settings`)
- **PMPro Plugin**: INTEGRATED for membership control and user authentication
- **Dashboard URL**: `/dashboard` page FUNCTIONAL with basic navigation shell
- **Security Framework**: Nonce verification, input sanitization, output escaping patterns IMPLEMENTED

**Technology Stack (FIXED CONSTRAINTS)**:
- **PHP 8.3.17**: REQUIRED for production hosting compatibility
- **MariaDB 10.5+**: Production database (10.4.32 on Local by Flywheel)
- **jQuery**: WordPress standard JavaScript library (NO React/Vue/Angular)
- **Apache Web Server**: Standard WordPress hosting environment
- **CSS Grid/Flexbox**: Modern CSS for responsive layouts
- **WordPress AJAX**: For dynamic content loading (wp_ajax hooks)

**External Services CONFIGURED**:
- **Supabase Project**: Active account with database and authentication ready
- **n8n Instance**: Running with webhook endpoints configured
- **Twilio Account**: Active with API keys stored in secure vault
- **Local by Flywheel**: Development environment ready for testing

---

## 🚧 Implementation Boundaries & Context

**MUST NOT CHANGE (Critical System Components)**:
- Existing database schema structure or table names
- Current `/dashboard` URL routing and WordPress permalink structure
- PMPro integration patterns and membership level checking
- WordPress plugin activation/deactivation hooks already implemented
- Security patterns from `.cursorrules` (maintain consistency)
- File structure established in Phase 1 (extend, don't rebuild)

**MUST FOLLOW (Non-Negotiable Requirements)**:
- **File Size Limit**: Maximum 600 lines per file (maintainability)
- **WordPress Standards**: All code must pass WP_DEBUG without errors
- **Security Patterns**: Every input sanitized, every output escaped, nonces verified
- **Mobile-First Design**: Touch-friendly interfaces across all screen sizes
- **Performance Budget**: Database queries under 100ms, page load under 3 seconds
- **Browser Support**: Chrome, Firefox, Safari, Edge (latest 2 versions)

**Context7 Usage Context**:
- **Primary Focus**: WordPress plugin development, PHP 8.3 features, API integrations
- **Secondary Focus**: Supabase real-time, n8n workflows, mobile responsive design
- **Avoid**: Generic web development, non-WordPress patterns, modern JS frameworks
- **Token Budget**: ~100,000 tokens daily across 5-week development cycle

---

## 🏗️ Phase 2 Implementation Context

**Current Implementation Phase**: Week-by-Week Template Development (5-week plan)  
**Integration Target**: Seamless extension of existing Dashboard Plugin v1.0.4  
**Business Value**: Transform basic dashboard into full CPaaS communication platform

**Week-by-Week Context**:
- **Week 1**: Foundation security and menu management (NO user-facing features)
- **Week 2**: First functional template with Twilio integration (VISIBLE to users)
- **Week 3**: Real-time data with Supabase integration (DYNAMIC content)
- **Week 4**: Canvas copy/paste functionality (INTERACTIVE features)
- **Week 5**: Performance optimization and final integration (PRODUCTION ready)

**Context7 Usage Per Week**:
- **Week 1**: WordPress architecture, PHP Sodium encryption, Settings API
- **Week 2**: Twilio integration, template development, responsive CSS
- **Week 3**: Supabase real-time, JavaScript drag-drop, cross-browser compatibility
- **Week 4**: n8n workflows, clipboard API, advanced JavaScript interactions
- **Week 5**: WordPress performance optimization, state management, production deployment

---

## 🎯 Overview

Context7 MCP provides up-to-date library documentation directly to Cursor IDE, optimized for your Dashboard Plugin development with WordPress, Supabase, n8n, and PHP 8.3.

### Key Benefits for Dashboard Plugin Development
- **90% Faster Documentation**: 2-3 minutes vs 10-15 minutes manual search
- **Always Current**: Live documentation from source repositories
- **Phase 2 Optimized**: Specifically configured for your 5-week implementation plan
- **Token Efficient**: Focused requests avoid irrelevant documentation
- **50-60% Faster Development**: Accelerated plugin development cycles

---

## 🔧 Setup Process

### Step 1: Configure Cursor MCP
Edit `~/.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "browser-tools": {
      "command": "npx",
      "args": ["-y", "@agentdeskai/browser-tools-mcp@1.2.0"],
      "enabled": true
    },
    "context7": {
      "command": "npx",
      "args": ["-y", "@upstash/context7-mcp@latest"],
      "enabled": true,
      "env": {
        "CONTEXT7_FOCUS": "dashboard-plugin-dev"
      }
    },
    "n8n-mcp": {
      "command": "npx", 
      "args": ["-y", "@n8n/mcp-server@latest"],
      "enabled": true
    }
  }
}
```

### Step 2: Restart Cursor
1. **Close Cursor completely**
2. **Restart Cursor**
3. **Wait 15-20 seconds** for MCP initialization
4. **Verify green lights** in Settings → MCP for all three servers

### Step 3: Test Setup
Test with dashboard-optimized query:
```bash
"Get WordPress plugin development documentation focusing on hooks and filters"
```

Expected response: WordPress-specific documentation with code examples.

---

## 📋 Daily Workflow Integration

### Starting Development Session
1. **Launch Cursor** (All MCPs start automatically)
2. **Check Settings → MCP**: Verify green lights for context7, browser-tools, n8n-mcp
3. **Reference Phase 2 plan**: Check current week/file context
4. **Test Context7**: Quick query to confirm connection

### Weekly Development Pattern

#### **Week 1: Foundation Setup**
**Files**: Menu manager, admin interface, security vault
**Primary Context7 Usage**: WordPress architecture, PHP 8.3 features, encryption
**Example Query**: `"Get WordPress plugin development documentation focusing on PSR-4 autoloading and activation hooks"`

#### **Week 2: Template Development**
**Files**: First template, Twilio API, admin editing
**Primary Context7 Usage**: Template development, API integration, admin interfaces
**Example Query**: `"Show Twilio API documentation focusing on PHP SDK and webhook security"`

#### **Week 3: Real-time Features**
**Files**: Supabase integration, analytics dashboard, canvas foundation
**Primary Context7 Usage**: Supabase real-time, JavaScript, responsive CSS
**Example Query**: `"Get Supabase JavaScript client documentation focusing on real-time subscriptions and authentication"`

#### **Week 4: Advanced Functionality**
**Files**: n8n integration, copy/paste engine, canvas interactions
**Primary Context7 Usage**: n8n workflows, advanced JavaScript, clipboard API
**Example Query**: `"Show n8n documentation focusing on webhook nodes and WordPress integration"`

#### **Week 5: Performance & Integration**
**Files**: Canvas manager, performance framework, persistence
**Primary Context7 Usage**: Performance optimization, state management, final integration
**Example Query**: `"Get WordPress performance optimization documentation focusing on caching and database queries"`

---

## 🎯 Technology Stack Configuration

### High Priority Libraries (Pre-configured)
✅ **WordPress Plugin Development**: Core hooks, filters, structure  
✅ **PHP 8.3**: Modern features, compatibility, best practices  
✅ **Supabase JavaScript Client**: Database operations, real-time, auth  
✅ **n8n Workflow Automation**: Webhooks, HTTP requests, integrations  
✅ **MariaDB/MySQL**: Query optimization, WordPress database patterns  

### Medium Priority (Use When Needed)
✅ **WordPress REST API**: Custom endpoints, authentication  
✅ **jQuery Documentation**: WordPress-standard JavaScript  
✅ **CSS Grid/Flexbox**: Modern styling for dashboard interfaces  
✅ **Twilio API**: SMS/Email communication features  

### Avoid (Efficiency Killers)
❌ **React/Vue/Angular**: Not relevant for WordPress plugin development  
❌ **Modern JavaScript Frameworks**: Outside WordPress ecosystem  
❌ **Generic Web Development**: Non-WordPress specific tutorials  

---

## 💡 Efficient Usage Patterns

### Context7 Request Optimization

#### **Phase 2 Optimized Format**
```bash
# Template
"Get [TECHNOLOGY] documentation focusing on [PHASE_2_NEED] for [CURRENT_FILE]"

# Examples by week
"Get WordPress plugin development documentation focusing on menu management for class-menu-manager.php"
"Show Supabase integration documentation focusing on real-time features for analytics-dashboard.php"
"Get n8n documentation focusing on webhook handling for class-api-n8n.php"
```

#### **Token Size Guidelines**
- **Quick Help**: 5,000 tokens (specific functions, immediate answers)
- **Implementation Guide**: 10,000-15,000 tokens (comprehensive how-to)
- **Full Reference**: 20,000+ tokens (complete API documentation)

#### **Problem-Solving Pattern**
```bash
# Template
"Show [TECHNOLOGY] troubleshooting documentation focusing on [ERROR_TYPE] and [SOLUTION_PATTERN]"

# Examples
"Get WordPress AJAX troubleshooting documentation focusing on nonce failures and authentication errors"
"Show Supabase error handling documentation focusing on connection timeouts and retry logic"
```

---

## 🔍 Troubleshooting

### Common Issues & Solutions

#### **Context7 Shows Red Light**
**Cause**: No internet connection or MCP server failed  
**Solution**:
1. Check internet connection (Context7 needs web access)
2. Restart Cursor completely
3. Wait 20 seconds for MCP initialization
4. Verify `mcp.json` syntax is correct

#### **Returns Generic Documentation**
**Cause**: Request too broad or missing focus keywords  
**Solution**:
1. Add "focusing on" clause to narrow results
2. Include WordPress context in request
3. Reference your current Phase 2 file context
4. Use specific technical terms from your stack

#### **Not Finding Expected Libraries**
**Cause**: Library not in Context7 database or incorrect name  
**Solution**:
1. Try alternative names (e.g., "WordPress" vs "wp")
2. Check official documentation for correct library name
3. Use library-specific terminology in requests

### Diagnostic Commands

#### **Test Context7 Connection**
```bash
"Get WordPress plugin development documentation focusing on hooks and filters"
```
*Expected*: WordPress-specific documentation with code examples

#### **Test Library Resolution**
```bash
"Show Supabase JavaScript client documentation"
```
*Expected*: Supabase-specific API documentation

#### **Test Phase 2 Optimization**
```bash
"Get PHP 8.3 documentation focusing on WordPress compatibility"
```
*Expected*: PHP 8.3 features with WordPress integration examples

---

## 🚀 MCP Integration Workflow

### Three-Tool Integration Pattern

#### **Context7 → Browser Tools → n8n MCP**
1. **Context7**: Get implementation documentation
2. **Code**: Implement based on documentation
3. **Browser Tools**: Debug and test functionality
4. **n8n MCP**: Set up and test workflow automation
5. **Context7**: Get optimization documentation

### Example Workflow: Implementing Supabase Real-time

#### **Step 1: Planning (Context7)**
```bash
"Get Supabase JavaScript client documentation focusing on real-time subscriptions and authentication"
```

#### **Step 2: Implementation**
Implement Supabase connection based on documentation

#### **Step 3: Testing (Browser Tools)**
```bash
"Show network requests"  # Monitor WebSocket connections
"Show console logs"      # Track real-time events
"Show console errors"    # Debug connection issues
```

#### **Step 4: Workflow Integration (n8n MCP)**
```bash
# Use n8n MCP to set up workflows that respond to Supabase changes
"List n8n nodes focusing on database triggers"
"Get node info for Supabase webhook integration"
```

#### **Step 5: Optimization (Context7)**
```bash
"Get Supabase performance optimization documentation focusing on connection pooling and error handling"
```

---

## 📊 Phase 2 Implementation Calendar

### Week-by-Week Context7 Usage

#### **Week 1 (Foundation)**
**Daily Context7 Focus**:
- Monday: Plugin architecture and PSR-4 autoloading
- Tuesday: WordPress Settings API and menu management
- Wednesday: PHP Sodium encryption and security
- Thursday: Admin interface development
- Friday: Integration testing and optimization

#### **Week 2 (Templates)**
**Daily Context7 Focus**:
- Monday: WordPress template development patterns
- Tuesday: Twilio API integration and security
- Wednesday: Admin interface enhancement
- Thursday: CSS design systems and responsive design
- Friday: Template integration and testing

#### **Week 3 (Real-time)**
**Daily Context7 Focus**:
- Monday: Supabase authentication and real-time setup
- Tuesday: JavaScript drag-drop and canvas foundation
- Wednesday: Mobile responsive design and touch optimization
- Thursday: Cross-browser compatibility and polyfills
- Friday: Performance testing and optimization

#### **Week 4 (Advanced)**
**Daily Context7 Focus**:
- Monday: n8n webhook integration and security
- Tuesday: JavaScript clipboard API and data serialization
- Wednesday: Advanced canvas interactions and multi-selection
- Thursday: Subdomain optimization and cross-domain handling
- Friday: Integration testing across all components

#### **Week 5 (Performance)**
**Daily Context7 Focus**:
- Monday: WordPress database optimization and JSON storage
- Tuesday: JavaScript performance monitoring and memory management
- Wednesday: Auto-save patterns and offline functionality
- Thursday: Performance framework implementation
- Friday: Final integration and performance validation

---

## 📚 Quick Reference Commands

### Essential Daily Commands
```bash
# Quick architecture check
"Get WordPress plugin development documentation focusing on [current_development_area]"

# API integration help
"Show [API_NAME] documentation focusing on [specific_feature] and WordPress integration"

# Performance optimization
"Get WordPress performance optimization documentation focusing on [performance_area]"

# Security implementation
"Show WordPress security documentation focusing on [security_concern] and best practices"
```

### Emergency Troubleshooting
```bash
# Database issues
"Get WordPress database troubleshooting documentation focusing on connection errors and query optimization"

# API connection problems
"Show API debugging documentation focusing on authentication failures and timeout handling"

# JavaScript errors
"Get JavaScript troubleshooting documentation focusing on event handling and browser compatibility"
```

---

## ⚙️ Configuration Optimization

### Environment Variables
Add to your shell profile (`.bashrc`, `.zshrc`):
```bash
export CONTEXT7_DASHBOARD_PHASE="2"
export CONTEXT7_CURRENT_WEEK="1"  # Update weekly
export CONTEXT7_FOCUS_AREA="wordpress-dashboard-plugin"
```

### Cursor Settings
Recommended Cursor settings for Context7 integration:
```json
{
  "context7.autoSuggest": true,
  "context7.focusArea": "wordpress-dashboard-plugin",
  "context7.tokenLimit": 15000,
  "context7.cacheEnabled": true
}
```

---

## 🎯 Success Metrics

### Expected Improvements with Context7
- **Documentation Lookup**: 2-3 minutes (vs 10-15 minutes manual)
- **Implementation Speed**: 50-60% faster development cycles
- **Code Quality**: Higher due to current best practices
- **Debugging Time**: 40-50% reduction in troubleshooting
- **Integration Success**: 90%+ first-attempt success rate

### Weekly Goals
- **Week 1**: Foundation setup with 100% Context7 integration
- **Week 2**: First template fully functional with API integration
- **Week 3**: Real-time features working across all browsers
- **Week 4**: Complete canvas functionality with copy/paste
- **Week 5**: Performance optimized and production ready

---

**Last Updated**: Dashboard Plugin Development - Phase 2  
**Setup Verified**: Windows 10, Cursor IDE, Node.js v20+, WordPress 6.8+  
**Tech Stack**: PHP 8.3.17, MariaDB 10.5, Supabase, n8n, Twilio 

---

## 🔄 **Flexible File Size Management & Dependency Tracking**

### **Quick Decision Matrix**

#### **When File Approaches 600 Lines**
1. **Evaluate**: Is functionality genuinely atomic?
2. **Document**: Add exception justification if atomic
3. **Split**: If natural boundaries exist, create separate files
4. **Monitor**: Set review date for future splitting consideration

#### **Exception Criteria (Up to 800 lines allowed)**
- Core API classes requiring comprehensive error handling
- Canvas management with multiple related interaction methods  
- Template files with extensive form validation and display logic
- Performance monitoring requiring multiple metric collection methods

### **Method Size Guidelines**
- **Target**: 60 lines maximum
- **Exception**: Up to 100 lines for complex data transformation, API integration with comprehensive error handling, canvas state serialization, or multi-step workflow processing

### **Required Documentation for Exceptions**
```php
/**
 * EXCEPTION: [X] lines (exceeds [limit])
 * JUSTIFICATION: [specific reason why split would create more complexity]
 * SPLIT_CONSIDERATION: [what split would require and why it's problematic]
 * REVIEW_DATE: [date to reconsider splitting]
 */
```

### **Dependency Tracking Strategy**

#### **File Header Documentation Pattern**
```php
/**
 * DEPENDENCY MAP
 * 
 * [filename]
 *   ├── DEPENDS ON: [file1] ([specific usage])
 *   ├── USED BY: [file2] ([specific integration])
 *   └── INTEGRATES: [file3] ([integration type])
 * 
 * SPLIT CANDIDATES:
 *   - [functionality1] ([estimated lines])
 *   - [functionality2] ([estimated lines])
 * 
 * LAST REVIEW: [date]
 * NEXT REVIEW: [date]
 */
```

#### **AI Assistant Context Comments**
```php
/**
 * AI_CONTEXT: [brief file purpose]
 * AI_DEPENDENCIES: 
 *   - MUST check [file1] for [specific patterns]
 *   - MUST validate [file2] integration
 * AI_INTEGRATION_POINTS:
 *   - [method1] connects to [external_file]
 *   - [method2] integrates with [external_system]
 * AI_VALIDATION: 
 *   - Test [specific functionality]
 *   - Verify [integration point]
 */
```

### **Weekly Dependency Audit Commands**
```bash
# Monday: Check file relationships
find includes/ -name "*.php" -exec grep -l "new\|extends\|implements" {} \;

# Wednesday: Validate JavaScript dependencies
find assets/js/ -name "*.js" -exec grep -l "import\|require" {} \;

# Friday: Review template integrations
find templates/ -name "*.php" -exec grep -l "get_template_part\|include" {} \;
```

### **Progressive Splitting Strategy**
1. **Phase 1**: Minimal Viable Implementation (single file)
2. **Phase 2**: Natural Breakpoint Splitting (when boundaries emerge)
3. **Phase 3**: Feature-Based Modularization (final organization)

### **Split Decision Criteria**

#### **Green Light (Split)**
- Method exceeds 100 lines AND has clear logical boundaries
- File exceeds 800 lines AND natural feature domains exist
- Multiple developers need to work on same functionality
- Code review identifies single responsibility violations

#### **Red Light (Don't Split)**
- Split would require complex shared state management
- Functionality is inherently atomic (complex algorithm)
- Split would create circular dependencies
- Testing would become significantly more complex

---

**Document Version**: Phase 2 Setup Reference  
**MCP Stack**: Context7 + Browser Tools + n8n MCP  
**File Size Strategy**: Flexible limits with exception documentation  
**Dependency Management**: Continuous tracking and validation 