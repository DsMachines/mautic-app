Here are the specific commands you can use:

## 🔍 HTML & DOM Inspection

### Get Selected Element
```bash
"Get the selected element from the browser"
```
- First, inspect an element in Chrome DevTools (right-click → Inspect)
- Then use this command to get detailed information about the selected DOM element

### Current Page Analysis
```bash
"Run accessibility audit"
```
- Analyzes HTML structure, semantic markup, ARIA labels
- Shows HTML accessibility issues and best practices
- Great for checking if your WordPress plugin's HTML output is properly structured

## 🎨 CSS Analysis & Performance

### CSS Performance Check
```bash
"Run performance audit"
```
- Analyzes CSS loading times, unused CSS, render-blocking resources
- Shows CSS optimization opportunities
- Critical for WordPress plugin frontend performance

### CSS Best Practices
```bash
"Run best practices audit"
```
- Checks CSS and HTML best practices
- Identifies inefficient CSS patterns
- WordPress-specific frontend optimization suggestions

### All-in-One Frontend Analysis
```bash
"Enter audit mode"
```
- Runs all audits: accessibility, performance, SEO, best practices
- Comprehensive HTML/CSS/JS analysis in one command

## 🛠️ JavaScript Debugging

### Console Output Monitoring
```bash
"Show console logs"
```
- Captures all `console.log()`, `console.info()`, `console.warn()` outputs
- Perfect for debugging WordPress AJAX calls and jQuery code

### JavaScript Error Detection
```bash
"Show console errors"
```
- Shows JavaScript errors, syntax errors, runtime exceptions
- Essential for WordPress plugin JavaScript debugging

### Network Request Analysis (AJAX/API)
```bash
"Get network requests"
```
- Shows all AJAX calls, API requests, resource loading
- Perfect for debugging WordPress `wp_ajax` calls and REST API requests

### Network Error Monitoring
```bash
"Get network error logs"
```
- Shows failed AJAX requests, 404s, API errors
- Critical for WordPress plugin integration debugging

## 📊 Real-Time Development Commands

### Live Debugging Session
```bash
"Enter debugger mode"
```
- Combines console logs, network monitoring, and error tracking
- Real-time debugging session for active development

### Performance Monitoring
```bash
"Run performance audit"
```
- Analyzes JavaScript execution time, CSS render blocking
- Shows resource loading waterfalls
- Perfect for WordPress plugin performance optimization

## 🎯 WordPress-Specific Use Cases

### Debug WordPress AJAX Calls
1. **Setup**: Make an AJAX call in your plugin
2. **Monitor**: `"Get network requests"`
3. **Debug**: `"Show console errors"`
4. **Verify**: `"Show console logs"`

### Debug WordPress Admin Interface
1. **Audit**: `"Run accessibility audit"` 
2. **Performance**: `"Run performance audit"`
3. **Errors**: `"Show console errors"`
4. **Network**: `"Get network requests"`

### Debug WordPress Frontend Widget/Shortcode
1. **Element**: Select your widget → `"Get selected element"`
2. **Console**: `"Show console logs"`
3. **Network**: `"Get network requests"`
4. **Performance**: `"Run performance audit"`

## 📋 Practical WordPress Development Workflow

### For New Plugin Feature Development:
```bash
# 1. Start development session
"Enter debugger mode"

# 2. Test specific functionality
"Show console logs"
"Get network requests"

# 3. Check for errors
"Show console errors" 

# 4. Optimize performance
"Run performance audit"
```

### For Frontend Debugging:
```bash
# 1. Check HTML structure
"Run accessibility audit"

# 2. Analyze CSS performance  
"Run performance audit"

# 3. Debug JavaScript
"Show console errors"
"Show console logs"

# 4. Monitor AJAX calls
"Get network requests"
```

### For Full Site Analysis:
```bash
# Complete analysis
"Enter audit mode"

# Then specific debugging
"Show console errors"
"Get network error logs"
```

## 🚀 Pro Tips for WordPress Plugin Development

### Real-time AJAX Debugging:
1. Open your WordPress admin/frontend page
2. Run: `"Get network requests"` 
3. Trigger your plugin's AJAX function
4. Check the network logs for your AJAX calls
5. Use: `"Show console errors"` to catch JavaScript issues

### CSS/JavaScript Asset Debugging:
1. Run: `"Run performance audit"`
2. Check "Opportunities" section for CSS/JS optimization
3. Look for render-blocking resources
4. Use results to optimize your plugin's asset loading

### Element-Specific Debugging:
1. Right-click on your plugin's HTML output → Inspect
2. Run: `"Get selected element"`
3. Analyze the DOM structure and properties
4. Perfect for debugging WordPress shortcode output

These commands give you comprehensive frontend debugging capabilities for your WordPress plugin development! The key is using them in combination - start with the audit commands for overview, then drill down with specific console/network commands for detailed debugging.