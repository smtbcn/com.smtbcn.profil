import { StyleSheet } from 'react-native';

export const profileScreenStyles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  header: {
    alignItems: 'center',
    paddingTop: 24,
    paddingBottom: 16,
  },
  avatar: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 2,
  },
  name: {
    marginTop: 12,
    fontSize: 18,
    fontWeight: '600',
  },
  buttonsContainer: {
    marginTop: 8,
  },
});
