# Browser Tools MCP Workflow Rules for WordPress Plugin Development

## 🎯 Browser Tools Integration for Dashboard & CPaaS Plugins

This file contains specialized Browser Tools MCP workflows for debugging, design capture, and performance optimization in WordPress plugin development.

## 🔍 Core Browser Tools Commands

### DOM & Element Inspection

#### Get Selected Element Information
```bash
"Get the selected element from the browser"
```
**Usage**: First inspect an element in Chrome DevTools (right-click → Inspect), then use this command
**Perfect for**: Analyzing GoHighLevel design elements, debugging WordPress plugin output

#### Page Structure Analysis
```bash
"Run accessibility audit"
```
**Usage**: Analyzes HTML structure, semantic markup, ARIA labels
**Perfect for**: Checking WordPress plugin HTML output, ensuring accessibility compliance

### JavaScript & Console Debugging

#### Monitor Console Output
```bash
"Show console logs"
```
**Usage**: Captures all `console.log()`, `console.info()`, `console.warn()` outputs
**Perfect for**: Debugging WordPress AJAX calls, jQuery code, real-time features

#### JavaScript Error Detection
```bash
"Show console errors"
```
**Usage**: Shows JavaScript errors, syntax errors, runtime exceptions
**Perfect for**: WordPress plugin JavaScript debugging, AJAX error tracking

#### Network Request Monitoring
```bash
"Get network requests"
```
**Usage**: Shows all AJAX calls, API requests, resource loading
**Perfect for**: Debugging WordPress `wp_ajax` calls, REST API requests, Supabase connections

#### Network Error Tracking
```bash
"Get network error logs"
```
**Usage**: Shows failed AJAX requests, 404s, API errors
**Perfect for**: WordPress plugin integration debugging, webhook failures

### Performance & Optimization

#### Performance Analysis
```bash
"Run performance audit"
```
**Usage**: Analyzes JavaScript execution time, CSS render blocking, resource loading
**Perfect for**: WordPress plugin performance optimization, dashboard loading speed

#### Best Practices Check
```bash
"Run best practices audit"
```
**Usage**: Checks CSS and HTML best practices, security headers
**Perfect for**: WordPress plugin code quality, security implementation

#### SEO Analysis
```bash
"Run SEO audit"
```
**Usage**: Analyzes meta tags, structured data, content optimization
**Perfect for**: WordPress plugin SEO impact, dashboard page optimization

### Comprehensive Analysis

#### All-in-One Audit
```bash
"Run audit mode"
```
**Usage**: Runs all audits: accessibility, performance, SEO, best practices
**Perfect for**: Complete WordPress plugin analysis, pre-deployment checks

#### Real-time Debugging Session
```bash
"Run debugger mode"
```
**Usage**: Combines console logs, network monitoring, and error tracking
**Perfect for**: Active development sessions, real-time debugging

## 🎨 GoHighLevel Design Capture Workflow

### Phase 1: Initial Design Analysis
```bash
# 1. Capture overall dashboard layout
"Take a screenshot of the current browser tab"

# 2. Analyze navigation structure
"Get the selected element from the browser"  # Select main navigation
"Run accessibility audit"                    # Check semantic structure

# 3. Check performance baseline
"Run performance audit"
```

### Phase 2: Component-Specific Analysis
```bash
# For horizontal navigation bar
"Get the selected element from the browser"  # Select nav elements
"Show console logs"                          # Monitor JavaScript interactions
"Get network requests"                       # Track AJAX navigation calls

# For vertical sidebar menu
"Get the selected element from the browser"  # Select sidebar elements
"Run best practices audit"                   # Check HTML structure
"Show console errors"                        # Check for layout issues

# For dashboard widgets/containers
"Get the selected element from the browser"  # Select widget elements
"Run accessibility audit"                    # Ensure proper markup
"Run performance audit"                      # Check rendering performance
```

### Phase 3: Interactive Features Testing
```bash
# Test user interactions
"Show console logs"                          # Monitor user actions
"Get network requests"                       # Track AJAX calls
"Show console errors"                        # Catch interaction errors

# Test responsive behavior
"Take a screenshot of the current browser tab"  # Mobile layout
"Run accessibility audit"                       # Mobile accessibility
"Run performance audit"                         # Mobile performance
```

## 🚀 WordPress Plugin Development Workflows

### Workflow 1: New Feature Development
```bash
# 1. Start development session
"Run debugger mode"

# 2. Test specific functionality
"Show console logs"
"Get network requests"

# 3. Check for errors
"Show console errors"

# 4. Optimize performance
"Run performance audit"
```

### Workflow 2: AJAX Debugging
```bash
# 1. Monitor network activity
"Get network requests"

# 2. Trigger AJAX functionality
# (perform user action)

# 3. Check for errors
"Show console errors"
"Get network error logs"

# 4. Verify success responses
"Show console logs"
```

### Workflow 3: Frontend Widget/Shortcode Testing
```bash
# 1. Analyze widget structure
"Get the selected element from the browser"  # Select widget output

# 2. Check console output
"Show console logs"

# 3. Monitor network calls
"Get network requests"

# 4. Performance check
"Run performance audit"
```

### Workflow 4: Security Testing
```bash
# 1. Check security headers
"Run best practices audit"

# 2. Test form submissions
"Get network requests"          # Monitor form data
"Show console errors"           # Check validation errors

# 3. Verify nonce implementation
"Show console logs"             # Check nonce values
"Get network error logs"        # Check unauthorized requests
```

## 🔧 CPaaS Integration Testing Workflows

### Supabase Real-time Testing
```bash
# 1. Monitor real-time connections
"Get network requests"
"Show console logs"

# 2. Test data synchronization
"Show console errors"           # Check connection errors
"Get network error logs"        # Monitor WebSocket issues

# 3. Performance impact
"Run performance audit"
```

### n8n Webhook Testing
```bash
# 1. Monitor webhook calls
"Get network requests"

# 2. Check webhook responses
"Show console logs"
"Show console errors"

# 3. Verify data transmission
"Get network error logs"
```

### Database Synchronization Testing
```bash
# 1. Test WordPress to Supabase sync
"Get network requests"          # Monitor API calls
"Show console logs"             # Check sync status

# 2. Test Supabase to WordPress sync
"Show console errors"           # Check for sync errors
"Get network error logs"        # Monitor failed requests
```

---

**Usage Instructions**: Use these workflows and commands throughout your WordPress plugin development process. Start each development session with the appropriate workflow, and use the specific commands based on your current development phase and debugging needs. 