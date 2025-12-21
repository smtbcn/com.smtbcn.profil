import { createContext, useContext } from 'react';
import { useColorScheme } from 'react-native';

const ThemeContext = createContext(null);

export const ThemeProvider = ({ children }) => {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';

  const theme = {
    isDark,
    colors: {
      background: isDark ? '#000000' : '#FFFFFF',
      text: isDark ? '#FFFFFF' : '#000000',
      textSecondary: isDark ? '#EEEEEE' : '#222222',
      border: isDark ? '#333333' : '#EEEEEE',
    },
  };

  return (
    <ThemeContext.Provider value={theme}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within ThemeProvider');
  }
  return context;
};
