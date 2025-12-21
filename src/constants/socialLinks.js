export const socialLinks = [
  { 
    label: 'GitHub', 
    url: 'https://github.com/smtbcn', 
    icon: 'github', 
    color: '#24292E' 
  },
  { 
    label: 'LinkedIn', 
    url: 'https://www.linkedin.com/in/smtbcn', 
    icon: 'linkedin', 
    color: '#0A66C2' 
  },
  { 
    label: 'X (Twitter)', 
    url: 'https://x.com/smtbcn', 
    icon: 'twitter', 
    color: '#1DA1F2' 
  },
  { 
    label: 'Instagram', 
    url: 'https://www.instagram.com/smtbcn', 
    icon: 'instagram', 
    color: '#E1306C' 
  },
  { 
    label: 'Facebook', 
    url: 'https://www.facebook.com/smtbcn', 
    icon: 'facebook', 
    color: '#1877F2' 
  },
];

export const getPath = (url) => {
  try {
    const path = url.replace(/^https?:\/\/[^/]+/, '');
    return path || '/';
  } catch (error) {
    return '/';
  }
};
