import { StyleSheet } from 'react-native';

export const navigationMenuStyles = StyleSheet.create({
  container: {
    borderBottomWidth: 1,
    borderBottomColor: '#333333',
  },
  scrollContent: {
    paddingHorizontal: 16,
    paddingVertical: 8,
  },
  tabButton: {
    marginRight: 8,
  },
  tab: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 8,
  },
  activeTab: {
    backgroundColor: '#1877F2',
  },
  pressedTab: {
    opacity: 0.7,
  },
  tabText: {
    fontSize: 15,
    fontWeight: '500',
  },
  activeTabText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
});
