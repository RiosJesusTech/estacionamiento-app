# TODO: Add Chatbot and Minimap Services to User Dashboard

## Completed Tasks
- [x] Analyze existing code and plan implementation
- [x] Confirm /api/packages endpoint exists

## Pending Tasks
- [ ] Update HomeView.vue: Add Chatbot and Minimap cards, adjust grid layout for 7 cards (desktop 4+3, mobile 2 columns)
- [ ] Create ChatbotView.vue: Simple chatbot interface with Q&A for spots, cost, packages; fetch from APIs; back button
- [ ] Create MinimapView.vue: Google Maps embed with provided link; back button
- [ ] Update router/index.js: Add routes for /chatbot and /minimap with user role restriction
- [ ] Test responsive grid layout on different screen sizes
- [ ] Verify API calls in chatbot work correctly
- [ ] Ensure back buttons navigate to /inicio
