FROM node:22

WORKDIR /var/www/html

# Copy frontend files
COPY . /var/www/html

# Install dependencies
RUN npm install

# Expose frontend port
EXPOSE 3000

# Run frontend dev server
CMD ["npm", "run", "dev"]
