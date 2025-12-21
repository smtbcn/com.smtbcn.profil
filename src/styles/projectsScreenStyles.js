import { StyleSheet } from 'react-native';

export const projectsScreenStyles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 24,
  },
  headerContainer: {
    paddingHorizontal: 16,
    paddingTop: 24,
    paddingBottom: 16,
    alignItems: 'center',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
  },
  reposContainer: {
    paddingHorizontal: 16,
    marginTop: 16,
  },
  subtitle: {
    fontSize: 14,
    marginBottom: 16,
  },
  repoCard: {
    borderWidth: 1,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
  },
  repoCardPressed: {
    opacity: 0.7,
  },
  repoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  repoName: {
    fontSize: 16,
    fontWeight: '600',
    marginLeft: 8,
    flex: 1,
  },
  repoDescription: {
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 12,
  },
  repoFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 16,
  },
  repoInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  repoInfoText: {
    fontSize: 12,
  },
  languageDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
  },
});
